<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class AdminLeaveController extends BaseController
{
    /**
     * Display leaves for admin management (filtered by ownership)
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $perPage = $request->get('per_page', 15);
        $status = $request->get('status'); // pending, approved, rejected
        $year = $request->get('year', date('Y'));
        $employee_id = $request->get('employee_id');

        $query = Leave::with(['employee.user', 'approver']);

        // Filter leaves based on user role
        if ($user->isSuperAdmin()) {
            // SuperAdmin sees all leaves
        } else {
            // Regular admin sees only leaves from employees they created
            $employeeIds = $user->createdEmployees()->pluck('id');
            $query->whereIn('employee_id', $employeeIds);
        }

        // Filter by status
        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        // Filter by year
        if ($year) {
            $query->whereYear('start_date', $year);
        }

        // Filter by specific employee
        if ($employee_id) {
            $query->where('employee_id', $employee_id);
        }

        $leaves = $query->latest()->paginate($perPage);

        // Transform data
        $leaves->getCollection()->transform(function ($leave) {
            return $this->transformAdminLeaveData($leave);
        });

        return $this->sendPaginatedResponse($leaves, 'Leave requests retrieved successfully');
    }

    /**
     * Display the specified leave for admin
     */
    public function show(Leave $leave): JsonResponse
    {
        $user = auth()->user();

        // Load relationships
        $leave->load(['employee.user', 'approver']);

        // Check if leave has employee
        if (!$leave->employee) {
            return $this->sendError('Leave request data is invalid. Employee not found.', [], 404);
        }

        // Check access permission for regular admin
        if (!$user->isSuperAdmin()) {
            $employeeIds = $user->createdEmployees()->pluck('id');
            if (!$employeeIds->contains($leave->employee_id)) {
                return $this->sendError('You can only view leaves from employees you created.', [], 403);
            }
        }

        $data = $this->transformAdminLeaveData($leave, true);

        return $this->sendResponse($data, 'Leave request retrieved successfully');
    }

    /**
     * Approve leave request
     */
    public function approve(Request $request, Leave $leave): JsonResponse
    {
        $user = auth()->user();

        // Load employee relationship
        $leave->load('employee');

        // Check if leave has employee
        if (!$leave->employee) {
            return $this->sendError('Leave request data is invalid. Employee not found.', [], 400);
        }

        // Check access permission for regular admin
        if (!$user->isSuperAdmin()) {
            $employeeIds = $user->createdEmployees()->pluck('id');
            if (!$employeeIds->contains($leave->employee_id)) {
                return $this->sendError('You can only approve leaves from employees you created.', [], 403);
            }
        }

        // Check if leave is still pending
        if (!$leave->isPending()) {
            return $this->sendError('Only pending leave requests can be approved.', [], 422);
        }

        $validator = Validator::make($request->all(), [
            'admin_notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        try {
            $leave->approve(auth()->user(), $request->admin_notes);

            $leave->load(['employee.user', 'approver']);

            $data = $this->transformAdminLeaveData($leave, true);

            return $this->sendResponse($data, 'Leave request approved successfully');
        } catch (\Exception $e) {
            return $this->sendError('Failed to approve leave request', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Reject leave request
     */
    public function reject(Request $request, Leave $leave): JsonResponse
    {
        $user = auth()->user();

        // Load employee relationship
        $leave->load('employee');

        // Check if leave has employee
        if (!$leave->employee) {
            return $this->sendError('Leave request data is invalid. Employee not found.', [], 400);
        }

        // Check access permission for regular admin
        if (!$user->isSuperAdmin()) {
            $employeeIds = $user->createdEmployees()->pluck('id');
            if (!$employeeIds->contains($leave->employee_id)) {
                return $this->sendError('You can only reject leaves from employees you created.', [], 403);
            }
        }

        // Check if leave is still pending
        if (!$leave->isPending()) {
            return $this->sendError('Only pending leave requests can be rejected.', [], 422);
        }

        $validator = Validator::make($request->all(), [
            'admin_notes' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        try {
            $leave->reject(auth()->user(), $request->admin_notes);

            $leave->load(['employee.user', 'approver']);

            $data = $this->transformAdminLeaveData($leave, true);

            return $this->sendResponse($data, 'Leave request rejected successfully');
        } catch (\Exception $e) {
            return $this->sendError('Failed to reject leave request', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Transform leave data for admin API response
     */
    private function transformAdminLeaveData(Leave $leave, bool $includeDetails = false): array
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
            'employee' => [
                'id' => $leave->employee->id,
                'full_name' => $leave->employee->full_name,
                'employee_id' => 'EMP' . str_pad($leave->employee->id, 4, '0', STR_PAD_LEFT),
                'user' => [
                    'id' => $leave->employee->user->id,
                    'name' => $leave->employee->user->name,
                    'email' => $leave->employee->user->email,
                ],
            ],
            'approver' => $leave->approver ? [
                'id' => $leave->approver->id,
                'name' => $leave->approver->name,
                'email' => $leave->approver->email,
                'user_type' => $leave->approver->user_type,
            ] : null,
            'created_at' => $leave->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $leave->updated_at->format('Y-m-d H:i:s'),
        ];

        // Include additional details if requested
        if ($includeDetails) {
            $data['employee']['leave_stats'] = [
                'total_used' => $leave->employee->getTotalLeaveDaysThisYear(),
                'remaining' => $leave->employee->getRemainingLeaveDays(),
                'annual_allowance' => 12,
                'usage_percentage' => round(($leave->employee->getTotalLeaveDaysThisYear() / 12) * 100)
            ];

            // Include creator info for SuperAdmin
            if (auth()->user()->isSuperAdmin() && $leave->employee->creator) {
                $data['employee']['creator'] = [
                    'id' => $leave->employee->creator->id,
                    'name' => $leave->employee->creator->name,
                    'email' => $leave->employee->creator->email,
                    'user_type' => $leave->employee->creator->user_type,
                ];
            }
        }

        return $data;
    }
}
