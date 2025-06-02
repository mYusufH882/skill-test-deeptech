<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

class EmployeeController extends BaseController
{
    /**
     * Display a listing of employees (filtered by ownership)
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');

        $query = Employee::with(['user', 'creator']);

        // Filter employees based on user role
        if ($user->isSuperAdmin()) {
            // SuperAdmin sees all employees
        } else {
            // Regular admin sees only employees they created
            $query->where('created_by', $user->id);
        }

        // Search functionality
        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        }

        $employees = $query->latest()->paginate($perPage);

        // Transform data
        $employees->getCollection()->transform(function ($employee) use ($user) {
            return $this->transformEmployeeData($employee, $user);
        });

        return $this->sendPaginatedResponse($employees, 'Employees retrieved successfully');
    }

    /**
     * Store a newly created employee
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'gender' => 'required|in:male,female',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'user_type' => 'employee',
            ]);

            // Create employee profile with created_by
            $employee = Employee::create([
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'address' => $request->address,
                'gender' => $request->gender,
                'created_by' => auth()->id(),
            ]);

            $employee->load(['user', 'creator']);

            $data = $this->transformEmployeeData($employee, auth()->user());

            return $this->sendResponse($data, 'Employee created successfully', 201);
        } catch (\Exception $e) {
            return $this->sendError('Failed to create employee', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified employee
     */
    public function show(Employee $employee): JsonResponse
    {
        $user = auth()->user();

        // Check access permission
        if (!$user->isSuperAdmin() && $employee->created_by !== $user->id) {
            return $this->sendError('Access denied. You can only view employees you created.', [], 403);
        }

        $employee->load(['user', 'leaves', 'creator']);

        $data = $this->transformEmployeeData($employee, $user, true);

        return $this->sendResponse($data, 'Employee retrieved successfully');
    }

    /**
     * Update the specified employee
     */
    public function update(Request $request, Employee $employee): JsonResponse
    {
        $user = auth()->user();

        // Check access permission
        if (!$user->isSuperAdmin() && $employee->created_by !== $user->id) {
            return $this->sendError('Access denied. You can only update employees you created.', [], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($employee->user_id)],
            'password' => 'nullable|string|min:8|confirmed',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'gender' => 'required|in:male,female',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        try {
            // Update user
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $employee->user->update($userData);

            // Update employee profile (created_by tidak berubah)
            $employee->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'address' => $request->address,
                'gender' => $request->gender,
                // created_by TIDAK diupdate, tetap milik creator asli
            ]);

            $employee->load(['user', 'creator']);

            $data = $this->transformEmployeeData($employee, $user);

            return $this->sendResponse($data, 'Employee updated successfully');
        } catch (\Exception $e) {
            return $this->sendError('Failed to update employee', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified employee
     */
    public function destroy(Employee $employee): JsonResponse
    {
        $user = auth()->user();

        // Check access permission
        if (!$user->isSuperAdmin() && $employee->created_by !== $user->id) {
            return $this->sendError('Access denied. You can only delete employees you created.', [], 403);
        }

        try {
            // Delete user (will cascade delete employee due to foreign key)
            $employee->user->delete();

            return $this->sendResponse([], 'Employee deleted successfully');
        } catch (\Exception $e) {
            return $this->sendError('Failed to delete employee', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Transform employee data for API response
     */
    private function transformEmployeeData(Employee $employee, User $currentUser, bool $includeLeaves = false): array
    {
        $data = [
            'id' => $employee->id,
            'user_id' => $employee->user_id,
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'full_name' => $employee->full_name,
            'phone' => $employee->phone,
            'address' => $employee->address,
            'gender' => $employee->gender,
            'employee_id' => 'EMP' . str_pad($employee->id, 4, '0', STR_PAD_LEFT),
            'user' => [
                'id' => $employee->user->id,
                'name' => $employee->user->name,
                'email' => $employee->user->email,
                'user_type' => $employee->user->user_type,
                'email_verified_at' => $employee->user->email_verified_at,
                'created_at' => $employee->user->created_at->format('Y-m-d H:i:s'),
            ],
            'leave_stats' => [
                'total_used' => $employee->getTotalLeaveDaysThisYear(),
                'remaining' => $employee->getRemainingLeaveDays(),
                'annual_allowance' => 12,
                'usage_percentage' => round(($employee->getTotalLeaveDaysThisYear() / 12) * 100)
            ],
            'created_at' => $employee->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $employee->updated_at->format('Y-m-d H:i:s'),
        ];

        // Include creator info only for SuperAdmin
        if ($currentUser->isSuperAdmin() && $employee->creator) {
            $data['creator'] = [
                'id' => $employee->creator->id,
                'name' => $employee->creator->name,
                'email' => $employee->creator->email,
                'user_type' => $employee->creator->user_type,
            ];
        }

        // Include leave details if requested
        if ($includeLeaves && $employee->relationLoaded('leaves')) {
            $data['leaves'] = $employee->leaves->map(function ($leave) {
                return [
                    'id' => $leave->id,
                    'reason' => $leave->reason,
                    'start_date' => $leave->start_date->format('Y-m-d'),
                    'end_date' => $leave->end_date->format('Y-m-d'),
                    'total_days' => $leave->total_days,
                    'status' => $leave->status,
                    'admin_notes' => $leave->admin_notes,
                    'approved_by' => $leave->approver ? $leave->approver->name : null,
                    'approved_at' => $leave->approved_at ? $leave->approved_at->format('Y-m-d H:i:s') : null,
                    'created_at' => $leave->created_at->format('Y-m-d H:i:s'),
                ];
            });
        }

        return $data;
    }
}
