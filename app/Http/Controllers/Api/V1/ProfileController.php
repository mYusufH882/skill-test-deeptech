<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class ProfileController extends BaseController
{
    /**
     * Display employee profile
     */
    public function show(Request $request): JsonResponse
    {
        $user = auth()->user();
        $employee = $user->employee;

        if (!$employee) {
            return $this->sendError('Employee profile not found. Please contact administrator.', [], 404);
        }

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
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'user_type' => $user->user_type,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
            ],
            'leave_stats' => [
                'total_used' => $employee->getTotalLeaveDaysThisYear(),
                'remaining' => $employee->getRemainingLeaveDays(),
                'annual_allowance' => 12,
                'usage_percentage' => round(($employee->getTotalLeaveDaysThisYear() / 12) * 100),
                'pending_requests' => $employee->leaves()->pending()->count(),
                'approved_requests' => $employee->leaves()->approved()->currentYear()->count(),
            ],
            'account_info' => [
                'account_created' => $user->created_at->format('M d, Y'),
                'profile_last_updated' => $employee->updated_at->format('M d, Y H:i'),
                'can_take_leave' => $employee->getRemainingLeaveDays() > 0,
            ],
            'created_at' => $employee->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $employee->updated_at->format('Y-m-d H:i:s'),
        ];

        return $this->sendResponse($data, 'Profile retrieved successfully');
    }

    /**
     * Update employee profile
     */
    public function update(Request $request): JsonResponse
    {
        $user = auth()->user();
        $employee = $user->employee;

        if (!$employee) {
            return $this->sendError('Employee profile not found. Please contact administrator.', [], 404);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        try {
            // Update employee profile
            $employee->update($request->only([
                'first_name',
                'last_name',
                'phone',
                'address'
            ]));

            // Update user name to match employee name
            $user->update([
                'name' => $request->first_name . ' ' . $request->last_name
            ]);

            // Return updated profile
            return $this->show($request);
        } catch (\Exception $e) {
            return $this->sendError('Failed to update profile', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get employee dashboard data
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = auth()->user();
        $employee = $user->employee;

        if (!$employee) {
            return $this->sendError('Employee profile not found. Please contact administrator.', [], 404);
        }

        // Get recent leaves
        $recentLeaves = $employee->leaves()
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($leave) {
                return [
                    'id' => $leave->id,
                    'reason' => $leave->reason,
                    'start_date' => $leave->start_date->format('Y-m-d'),
                    'end_date' => $leave->end_date->format('Y-m-d'),
                    'total_days' => $leave->total_days,
                    'status' => $leave->status,
                    'created_at' => $leave->created_at->format('Y-m-d H:i:s'),
                ];
            });

        $data = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'employee_id' => 'EMP' . str_pad($employee->id, 4, '0', STR_PAD_LEFT),
            ],
            'stats' => [
                'total_leave_days_used' => $employee->getTotalLeaveDaysThisYear(),
                'remaining_leave_days' => $employee->getRemainingLeaveDays(),
                'pending_requests' => $employee->leaves()->pending()->count(),
                'approved_requests' => $employee->leaves()->approved()->currentYear()->count(),
                'annual_allowance' => 12,
                'usage_percentage' => round(($employee->getTotalLeaveDaysThisYear() / 12) * 100),
            ],
            'recent_leaves' => $recentLeaves,
            'leave_usage_progress' => [
                'used' => $employee->getTotalLeaveDaysThisYear(),
                'total' => 12,
                'percentage' => round(($employee->getTotalLeaveDaysThisYear() / 12) * 100),
                'status' => $this->getUsageStatus($employee->getTotalLeaveDaysThisYear()),
                'can_request_leave' => $employee->getRemainingLeaveDays() > 0,
            ],
            'quick_actions' => [
                'can_request_leave' => $employee->getRemainingLeaveDays() > 0,
                'has_pending_requests' => $employee->leaves()->pending()->exists(),
                'remaining_days' => $employee->getRemainingLeaveDays(),
            ]
        ];

        return $this->sendResponse($data, 'Dashboard data retrieved successfully');
    }

    /**
     * Get leave usage status
     */
    private function getUsageStatus(int $usedDays): string
    {
        $percentage = ($usedDays / 12) * 100;

        if ($percentage >= 100) {
            return 'exceeded';
        } elseif ($percentage >= 80) {
            return 'high';
        } elseif ($percentage >= 50) {
            return 'moderate';
        } else {
            return 'low';
        }
    }
}
