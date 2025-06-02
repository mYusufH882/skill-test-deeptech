<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

class AdminController extends BaseController
{
    /**
     * Display a listing of admins (SuperAdmin only)
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');

        $query = Admin::with('user')->whereHas('user', function ($q) {
            $q->where('user_type', 'admin');
        });

        // Search functionality
        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%");
        }

        $admins = $query->latest()->paginate($perPage);

        // Transform data
        $admins->getCollection()->transform(function ($admin) {
            return [
                'id' => $admin->id,
                'user_id' => $admin->user_id,
                'first_name' => $admin->first_name,
                'last_name' => $admin->last_name,
                'full_name' => $admin->full_name,
                'birth_date' => $admin->birth_date->format('Y-m-d'),
                'gender' => $admin->gender,
                'age' => $admin->age,
                'user' => [
                    'id' => $admin->user->id,
                    'name' => $admin->user->name,
                    'email' => $admin->user->email,
                    'user_type' => $admin->user->user_type,
                    'email_verified_at' => $admin->user->email_verified_at,
                    'created_at' => $admin->user->created_at->format('Y-m-d H:i:s'),
                ],
                'created_at' => $admin->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $admin->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        return $this->sendPaginatedResponse($admins, 'Admins retrieved successfully');
    }

    /**
     * Store a newly created admin
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'required|date|before:today',
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
                'user_type' => 'admin',
            ]);

            // Create admin profile
            $admin = Admin::create([
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
            ]);

            $admin->load('user');

            $data = [
                'id' => $admin->id,
                'user_id' => $admin->user_id,
                'first_name' => $admin->first_name,
                'last_name' => $admin->last_name,
                'full_name' => $admin->full_name,
                'birth_date' => $admin->birth_date->format('Y-m-d'),
                'gender' => $admin->gender,
                'age' => $admin->age,
                'user' => [
                    'id' => $admin->user->id,
                    'name' => $admin->user->name,
                    'email' => $admin->user->email,
                    'user_type' => $admin->user->user_type,
                    'created_at' => $admin->user->created_at->format('Y-m-d H:i:s'),
                ],
                'created_at' => $admin->created_at->format('Y-m-d H:i:s'),
            ];

            return $this->sendResponse($data, 'Admin created successfully', 201);
        } catch (\Exception $e) {
            return $this->sendError('Failed to create admin', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified admin
     */
    public function show(Admin $admin): JsonResponse
    {
        $admin->load('user');

        $data = [
            'id' => $admin->id,
            'user_id' => $admin->user_id,
            'first_name' => $admin->first_name,
            'last_name' => $admin->last_name,
            'full_name' => $admin->full_name,
            'birth_date' => $admin->birth_date->format('Y-m-d'),
            'gender' => $admin->gender,
            'age' => $admin->age,
            'user' => [
                'id' => $admin->user->id,
                'name' => $admin->user->name,
                'email' => $admin->user->email,
                'user_type' => $admin->user->user_type,
                'email_verified_at' => $admin->user->email_verified_at,
                'created_at' => $admin->user->created_at->format('Y-m-d H:i:s'),
            ],
            'created_at' => $admin->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $admin->updated_at->format('Y-m-d H:i:s'),
        ];

        return $this->sendResponse($data, 'Admin retrieved successfully');
    }

    /**
     * Update the specified admin
     */
    public function update(Request $request, Admin $admin): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($admin->user_id)],
            'password' => 'nullable|string|min:8|confirmed',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'required|date|before:today',
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

            $admin->user->update($userData);

            // Update admin profile
            $admin->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
            ]);

            $admin->load('user');

            $data = [
                'id' => $admin->id,
                'user_id' => $admin->user_id,
                'first_name' => $admin->first_name,
                'last_name' => $admin->last_name,
                'full_name' => $admin->full_name,
                'birth_date' => $admin->birth_date->format('Y-m-d'),
                'gender' => $admin->gender,
                'age' => $admin->age,
                'user' => [
                    'id' => $admin->user->id,
                    'name' => $admin->user->name,
                    'email' => $admin->user->email,
                    'user_type' => $admin->user->user_type,
                    'updated_at' => $admin->user->updated_at->format('Y-m-d H:i:s'),
                ],
                'updated_at' => $admin->updated_at->format('Y-m-d H:i:s'),
            ];

            return $this->sendResponse($data, 'Admin updated successfully');
        } catch (\Exception $e) {
            return $this->sendError('Failed to update admin', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified admin
     */
    public function destroy(Admin $admin): JsonResponse
    {
        try {
            // Delete user (will cascade delete admin due to foreign key)
            $admin->user->delete();

            return $this->sendResponse([], 'Admin deleted successfully');
        } catch (\Exception $e) {
            return $this->sendError('Failed to delete admin', ['error' => $e->getMessage()], 500);
        }
    }
}
