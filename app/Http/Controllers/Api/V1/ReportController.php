<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Admin;
use App\Models\Employee;
use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReportController extends BaseController
{
    /**
     * Get admin dashboard statistics
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = auth()->user();

        // Get stats based on user role
        if ($user->isSuperAdmin()) {
            // SuperAdmin sees all data
            $stats = [
                'total_admins' => Admin::count(),
                'total_employees' => Employee::count(),
                'pending_leaves' => Leave::pending()->count(),
                'approved_leaves_today' => Leave::approved()->whereDate('created_at', today())->count(),
                'total_approved_leaves' => Leave::approved()->currentYear()->count(),
                'total_leave_days' => Leave::approved()->currentYear()->get()->sum('total_days'),
            ];

            $recentLeaves = Leave::with(['employee.user'])
                ->latest()
                ->take(5)
                ->get();

            $topEmployeesByLeave = Employee::with('user')
                ->get()
                ->sortByDesc(function ($employee) {
                    return $employee->getTotalLeaveDaysThisYear();
                })
                ->take(5)
                ->values();
        } else {
            // Regular admin sees only their data
            $employeeIds = $user->createdEmployees()->pluck('id');

            $stats = [
                'total_admins' => 1, // Only themselves
                'total_employees' => $user->createdEmployees()->count(),
                'pending_leaves' => Leave::pending()->whereIn('employee_id', $employeeIds)->count(),
                'approved_leaves_today' => Leave::approved()
                    ->whereIn('employee_id', $employeeIds)
                    ->whereDate('created_at', today())
                    ->count(),
                'total_approved_leaves' => Leave::approved()->currentYear()->whereIn('employee_id', $employeeIds)->count(),
                'total_leave_days' => Leave::approved()->currentYear()->whereIn('employee_id', $employeeIds)->get()->sum('total_days'),
            ];

            $recentLeaves = Leave::with(['employee.user'])
                ->whereIn('employee_id', $employeeIds)
                ->latest()
                ->take(5)
                ->get();

            $topEmployeesByLeave = Employee::with('user')
                ->whereIn('id', $employeeIds)
                ->get()
                ->sortByDesc(function ($employee) {
                    return $employee->getTotalLeaveDaysThisYear();
                })
                ->take(5)
                ->values();
        }

        // Transform recent leaves
        $recentLeavesData = $recentLeaves->map(function ($leave) {
            return [
                'id' => $leave->id,
                'employee' => [
                    'id' => $leave->employee->id,
                    'full_name' => $leave->employee->full_name,
                    'email' => $leave->employee->user->email,
                ],
                'start_date' => $leave->start_date->format('Y-m-d'),
                'end_date' => $leave->end_date->format('Y-m-d'),
                'total_days' => $leave->total_days,
                'status' => $leave->status,
                'created_at' => $leave->created_at->format('Y-m-d H:i:s'),
            ];
        });

        // Transform top employees
        $topEmployeesData = $topEmployeesByLeave->map(function ($employee) {
            return [
                'id' => $employee->id,
                'full_name' => $employee->full_name,
                'email' => $employee->user->email,
                'total_used' => $employee->getTotalLeaveDaysThisYear(),
                'remaining' => $employee->getRemainingLeaveDays(),
                'usage_percentage' => round(($employee->getTotalLeaveDaysThisYear() / 12) * 100),
            ];
        });

        $data = [
            'stats' => $stats,
            'recent_leaves' => $recentLeavesData,
            'top_employees_by_leave' => $topEmployeesData,
            'user_scope' => $user->isSuperAdmin() ? 'system_wide' : 'my_employees',
        ];

        return $this->sendResponse($data, 'Dashboard statistics retrieved successfully');
    }

    /**
     * Get employee leave report
     */
    public function employees(Request $request): JsonResponse
    {
        $user = auth()->user();
        $year = $request->get('year', date('Y'));

        if ($user->isSuperAdmin()) {
            // SuperAdmin sees all employees with leaves
            $employees = Employee::with(['user', 'leaves' => function ($query) use ($year) {
                $query->approved()->whereYear('start_date', $year);
            }, 'creator'])->get();
        } else {
            // Regular admin sees only their employees
            $employeeIds = $user->createdEmployees()->pluck('id');

            $employees = Employee::with(['user', 'leaves' => function ($query) use ($year) {
                $query->approved()->whereYear('start_date', $year);
            }, 'creator'])
                ->whereIn('id', $employeeIds)
                ->get();
        }

        // Transform employee data
        $employeesData = $employees->map(function ($employee) use ($user) {
            $totalUsed = $employee->getTotalLeaveDaysThisYear();
            $remaining = $employee->getRemainingLeaveDays();
            $usagePercent = round(($totalUsed / 12) * 100);

            $data = [
                'id' => $employee->id,
                'full_name' => $employee->full_name,
                'employee_id' => 'EMP' . str_pad($employee->id, 4, '0', STR_PAD_LEFT),
                'email' => $employee->user->email,
                'phone' => $employee->phone,
                'gender' => $employee->gender,
                'total_leaves' => $employee->leaves->count(),
                'total_used' => $totalUsed,
                'remaining' => $remaining,
                'usage_percentage' => $usagePercent,
                'status' => $this->getUsageStatus($usagePercent),
                'created_at' => $employee->created_at->format('Y-m-d'),
            ];

            // Include creator info only for SuperAdmin
            if ($user->isSuperAdmin() && $employee->creator) {
                $data['creator'] = [
                    'id' => $employee->creator->id,
                    'name' => $employee->creator->name,
                    'email' => $employee->creator->email,
                    'user_type' => $employee->creator->user_type,
                ];
            }

            return $data;
        });

        // Calculate summary statistics
        $summary = [
            'total_employees' => $employees->count(),
            'average_usage' => $employees->count() > 0 ? round($employees->avg(function ($emp) {
                return ($emp->getTotalLeaveDaysThisYear() / 12) * 100;
            })) : 0,
            'high_usage_count' => $employees->filter(function ($emp) {
                return ($emp->getTotalLeaveDaysThisYear() / 12) * 100 >= 80;
            })->count(),
            'low_usage_count' => $employees->filter(function ($emp) {
                return ($emp->getTotalLeaveDaysThisYear() / 12) * 100 < 50;
            })->count(),
            'exceeded_count' => $employees->filter(function ($emp) {
                return ($emp->getTotalLeaveDaysThisYear() / 12) * 100 >= 100;
            })->count(),
        ];

        $data = [
            'employees' => $employeesData,
            'summary' => $summary,
            'year' => $year,
            'user_scope' => $user->isSuperAdmin() ? 'system_wide' : 'my_employees',
        ];

        return $this->sendResponse($data, 'Employee leave report retrieved successfully');
    }

    /**
     * Get leave statistics report
     */
    public function leaves(Request $request): JsonResponse
    {
        $user = auth()->user();
        $year = $request->get('year', date('Y'));
        $status = $request->get('status'); // pending, approved, rejected

        $query = Leave::with(['employee.user', 'approver']);

        // Filter leaves based on user role
        if ($user->isSuperAdmin()) {
            // SuperAdmin sees all leaves
        } else {
            // Regular admin sees only leaves from employees they created
            $employeeIds = $user->createdEmployees()->pluck('id');
            $query->whereIn('employee_id', $employeeIds);
        }

        // Filter by year
        if ($year) {
            $query->whereYear('start_date', $year);
        }

        // Filter by status
        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        $leaves = $query->latest()->get();

        // Transform leave data
        $leavesData = $leaves->map(function ($leave) {
            return [
                'id' => $leave->id,
                'employee' => [
                    'id' => $leave->employee->id,
                    'full_name' => $leave->employee->full_name,
                    'employee_id' => 'EMP' . str_pad($leave->employee->id, 4, '0', STR_PAD_LEFT),
                    'email' => $leave->employee->user->email,
                ],
                'reason' => $leave->reason,
                'start_date' => $leave->start_date->format('Y-m-d'),
                'end_date' => $leave->end_date->format('Y-m-d'),
                'total_days' => $leave->total_days,
                'status' => $leave->status,
                'admin_notes' => $leave->admin_notes,
                'approver' => $leave->approver ? [
                    'id' => $leave->approver->id,
                    'name' => $leave->approver->name,
                    'user_type' => $leave->approver->user_type,
                ] : null,
                'approved_at' => $leave->approved_at ? $leave->approved_at->format('Y-m-d H:i:s') : null,
                'created_at' => $leave->created_at->format('Y-m-d H:i:s'),
            ];
        });

        // Calculate statistics
        $statistics = [
            'total_requests' => $leaves->count(),
            'pending_count' => $leaves->where('status', 'pending')->count(),
            'approved_count' => $leaves->where('status', 'approved')->count(),
            'rejected_count' => $leaves->where('status', 'rejected')->count(),
            'total_days_approved' => $leaves->where('status', 'approved')->sum('total_days'),
            'average_days_per_request' => $leaves->count() > 0 ? round($leaves->avg('total_days'), 1) : 0,
        ];

        $data = [
            'leaves' => $leavesData,
            'statistics' => $statistics,
            'filters' => [
                'year' => $year,
                'status' => $status,
            ],
            'user_scope' => $user->isSuperAdmin() ? 'system_wide' : 'my_employees',
        ];

        return $this->sendResponse($data, 'Leave statistics report retrieved successfully');
    }

    /**
     * Get leave usage status
     */
    private function getUsageStatus(int $usagePercent): string
    {
        if ($usagePercent >= 100) {
            return 'exceeded';
        } elseif ($usagePercent >= 80) {
            return 'high_usage';
        } elseif ($usagePercent >= 50) {
            return 'moderate';
        } else {
            return 'low_usage';
        }
    }
}
