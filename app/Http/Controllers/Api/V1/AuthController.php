<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class AuthController extends BaseController
{
    /**
     * Login user and create token
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return $this->sendError('Invalid credentials', [], 401);
        }

        $user = Auth::user();

        // Create token
        $token = $user->createToken('API Token')->plainTextToken;

        // Prepare user data with profile
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'user_type' => $user->user_type,
            'profile' => null,
            'permissions' => $this->getUserPermissions($user)
        ];

        // Load profile based on user type
        if ($user->isAdmin()) {
            $userData['profile'] = $user->admin ? [
                'id' => $user->admin->id,
                'first_name' => $user->admin->first_name,
                'last_name' => $user->admin->last_name,
                'full_name' => $user->admin->full_name,
                'birth_date' => $user->admin->birth_date->format('Y-m-d'),
                'gender' => $user->admin->gender,
                'age' => $user->admin->age
            ] : null;
        } elseif ($user->isEmployee()) {
            $userData['profile'] = $user->employee ? [
                'id' => $user->employee->id,
                'first_name' => $user->employee->first_name,
                'last_name' => $user->employee->last_name,
                'full_name' => $user->employee->full_name,
                'phone' => $user->employee->phone,
                'address' => $user->employee->address,
                'gender' => $user->employee->gender,
                'employee_id' => 'EMP' . str_pad($user->employee->id, 4, '0', STR_PAD_LEFT)
            ] : null;
        }

        $data = [
            'user' => $userData,
            'token' => $token,
            'token_type' => 'Bearer'
        ];

        return $this->sendResponse($data, 'Login successful');
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'user_type' => $user->user_type,
            'profile' => null,
            'permissions' => $this->getUserPermissions($user)
        ];

        // Load profile based on user type
        if ($user->isAdmin()) {
            $userData['profile'] = $user->admin ? [
                'id' => $user->admin->id,
                'first_name' => $user->admin->first_name,
                'last_name' => $user->admin->last_name,
                'full_name' => $user->admin->full_name,
                'birth_date' => $user->admin->birth_date->format('Y-m-d'),
                'gender' => $user->admin->gender,
                'age' => $user->admin->age
            ] : null;
        } elseif ($user->isEmployee()) {
            $userData['profile'] = $user->employee ? [
                'id' => $user->employee->id,
                'first_name' => $user->employee->first_name,
                'last_name' => $user->employee->last_name,
                'full_name' => $user->employee->full_name,
                'phone' => $user->employee->phone,
                'address' => $user->employee->address,
                'gender' => $user->employee->gender,
                'employee_id' => 'EMP' . str_pad($user->employee->id, 4, '0', STR_PAD_LEFT),
                'leave_stats' => [
                    'total_used' => $user->employee->getTotalLeaveDaysThisYear(),
                    'remaining' => $user->employee->getRemainingLeaveDays(),
                    'annual_allowance' => 12
                ]
            ] : null;
        }

        return $this->sendResponse($userData, 'User data retrieved successfully');
    }

    /**
     * Update current user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        // Update user name
        $user->update([
            'name' => $request->name
        ]);

        // Update profile based on user type
        if ($user->isAdmin() && $user->admin) {
            $profileValidator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'birth_date' => 'required|date|before:today',
                'gender' => 'required|in:male,female',
            ]);

            if ($profileValidator->fails()) {
                return $this->sendValidationError($profileValidator->errors());
            }

            $user->admin->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
            ]);
        } elseif ($user->isEmployee() && $user->employee) {
            $profileValidator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'address' => 'required|string',
            ]);

            if ($profileValidator->fails()) {
                return $this->sendValidationError($profileValidator->errors());
            }

            $user->employee->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);
        }

        return $this->me($request);
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->sendResponse([], 'Successfully logged out');
    }

    /**
     * Register new user (optional - for employee self-registration)
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => 'employee', // Default to employee
        ]);

        $token = $user->createToken('API Token')->plainTextToken;

        $data = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'user_type' => $user->user_type,
            ],
            'token' => $token,
            'token_type' => 'Bearer'
        ];

        return $this->sendResponse($data, 'Registration successful', 201);
    }

    /**
     * Get user permissions based on role
     */
    private function getUserPermissions(User $user): array
    {
        $permissions = [];

        if ($user->isSuperAdmin()) {
            $permissions = [
                'manage_admins',
                'manage_employees',
                'manage_leaves',
                'view_reports',
                'system_admin'
            ];
        } elseif ($user->isRegularAdmin()) {
            $permissions = [
                'manage_employees',
                'manage_leaves',
                'view_reports'
            ];
        } elseif ($user->isEmployee()) {
            $permissions = [
                'manage_own_leaves',
                'view_own_profile'
            ];
        }

        return $permissions;
    }
}
