<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class LeaveController extends BaseController
{
    /**
     * Display employee's leave requests
     */
    public function index(Request $request): JsonResponse
    {
        $employee = auth()->user()->employee;

        // Check if user has employee profile
        if (!$employee) {
            return $this->sendError('Employee profile not found. Please contact administrator.', [], 403);
        }

        $perPage = $request->get('per_page', 15);
        $status = $request->get('status'); // pending, approved, rejected
        $year = $request->get('year', date('Y'));

        $query = $employee->leaves()->with(['approver']);

        // Filter by status
        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        // Filter by year
        if ($year) {
            $query->whereYear('start_date', $year);
        }

        $leaves = $query->latest()->paginate($perPage);

        // Transform data
        $leaves->getCollection()->transform(function ($leave) {
            return $this->transformLeaveData($leave);
        });

        return $this->sendPaginatedResponse($leaves, 'Leave requests retrieved successfully');
    }

    /**
     * Store a newly created leave request
     */
    public function store(Request $request): JsonResponse
    {
        $employee = auth()->user()->employee;

        // Check if user has employee profile
        if (!$employee) {
            return $this->sendError('Employee profile not found. Please contact administrator.', [], 400);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:1000',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        // Business Rule 1: Check if employee has enough remaining leave days
        if ($employee->getRemainingLeaveDays() < $totalDays) {
            return $this->sendError(
                'Insufficient leave days',
                ['start_date' => ['You have ' . $employee->getRemainingLeaveDays() . ' days remaining.']],
                422
            );
        }

        // Business Rule 2: Check if employee already has leave in the same month
        if (!$employee->canTakeLeaveInMonth($startDate->year, $startDate->month)) {
            return $this->sendError(
                'Leave conflict',
                ['start_date' => ['You already have approved leave in ' . $startDate->format('F Y') . '. Only one leave per month is allowed.']],
                422
            );
        }

        // Check if leave spans multiple months
        if ($startDate->month !== $endDate->month) {
            if (!$employee->canTakeLeaveInMonth($endDate->year, $endDate->month)) {
                return $this->sendError(
                    'Leave conflict',
                    ['end_date' => ['You already have approved leave in ' . $endDate->format('F Y') . '. Only one leave per month is allowed.']],
                    422
                );
            }
        }

        try {
            $leave = Leave::create([
                'employee_id' => $employee->id,
                'reason' => $request->reason,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => 'pending',
            ]);

            $leave->load('approver');

            $data = $this->transformLeaveData($leave);

            return $this->sendResponse($data, 'Leave request submitted successfully', 201);
        } catch (\Exception $e) {
            return $this->sendError('Failed to create leave request', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified leave
     */
    public function show(Leave $leave): JsonResponse
    {
        // Load the employee relationship to avoid null errors
        $leave->load(['employee.user', 'approver']);

        // Check if leave has employee (data integrity check)
        if (!$leave->employee) {
            return $this->sendError('Leave request data is invalid. Employee not found.', [], 404);
        }

        // Check authorization for employees
        if (auth()->user()->isEmployee()) {
            // Check if user has employee profile
            $currentEmployee = auth()->user()->employee;
            if (!$currentEmployee) {
                return $this->sendError('Employee profile not found. Please contact administrator.', [], 403);
            }

            // Check if this leave belongs to current employee
            if ($leave->employee_id !== $currentEmployee->id) {
                return $this->sendError('You can only view your own leave requests.', [], 403);
            }
        }

        $data = $this->transformLeaveData($leave, true);

        return $this->sendResponse($data, 'Leave request retrieved successfully');
    }

    /**
     * Update the specified leave (Employee can only edit pending leaves)
     */
    public function update(Request $request, Leave $leave): JsonResponse
    {
        // Load the employee relationship
        $leave->load('employee.user');

        // Check if leave has employee
        if (!$leave->employee) {
            return $this->sendError('Leave request data is invalid. Employee not found.', [], 400);
        }

        // Check if user has employee profile
        $currentEmployee = auth()->user()->employee;
        if (!$currentEmployee) {
            return $this->sendError('Employee profile not found. Please contact administrator.', [], 403);
        }

        // Check authorization
        if ($leave->employee_id !== $currentEmployee->id) {
            return $this->sendError('You can only edit your own leave requests.', [], 403);
        }

        // Only allow editing pending leaves
        if (!$leave->isPending()) {
            return $this->sendError('You can only edit pending leave requests.', [], 422);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:1000',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        $employee = $leave->employee;
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        // Calculate remaining days excluding current leave
        $currentLeaveDays = $leave->total_days;
        $availableDays = $employee->getRemainingLeaveDays() + $currentLeaveDays;

        if ($availableDays < $totalDays) {
            return $this->sendError(
                'Insufficient leave days',
                ['start_date' => ['You have ' . $availableDays . ' days available.']],
                422
            );
        }

        try {
            $leave->update([
                'reason' => $request->reason,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);

            $leave->load('approver');

            $data = $this->transformLeaveData($leave);

            return $this->sendResponse($data, 'Leave request updated successfully');
        } catch (\Exception $e) {
            return $this->sendError('Failed to update leave request', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Transform leave data for API response
     */
    private function transformLeaveData(Leave $leave, bool $includeEmployee = false): array
    {
        $data = [
            'id' => $leave->id,
            'employee_id' => $leave->employee_id,
            'reason' => $leave->reason,
            'start_date' => $leave->start_date->format('Y-m-d'),
            'end_date' => $leave->end_date->format('Y-m-d'),
            'total_days' => $leave->total_days,
            'status' => $leave->status,
            'admin_notes' => $leave->admin_notes,
            'approved_by' => $leave->approved_by,
            'approved_at' => $leave->approved_at ? $leave->approved_at->format('Y-m-d H:i:s') : null,
            'approver' => $leave->approver ? [
                'id' => $leave->approver->id,
                'name' => $leave->approver->name,
                'email' => $leave->approver->email,
            ] : null,
            'created_at' => $leave->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $leave->updated_at->format('Y-m-d H:i:s'),
        ];

        // Include employee details if requested (for admin views)
        if ($includeEmployee && $leave->employee) {
            $data['employee'] = [
                'id' => $leave->employee->id,
                'full_name' => $leave->employee->full_name,
                'employee_id' => 'EMP' . str_pad($leave->employee->id, 4, '0', STR_PAD_LEFT),
                'user' => [
                    'id' => $leave->employee->user->id,
                    'name' => $leave->employee->user->name,
                    'email' => $leave->employee->user->email,
                ],
            ];
        }

        return $data;
    }
}
