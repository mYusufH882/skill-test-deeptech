<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\User;
use App\Models\Employee;
use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Admin Dashboard
     */
    public function dashboard()
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
            ];

            $recent_leaves = Leave::with(['employee.user'])
                ->latest()
                ->take(5)
                ->get();
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
            ];

            $recent_leaves = Leave::with(['employee.user'])
                ->whereIn('employee_id', $employeeIds)
                ->latest()
                ->take(5)
                ->get();
        }

        return view('admin.dashboard', compact('stats', 'recent_leaves'));
    }

    /**
     * Display a listing of admins.
     */
    public function index()
    {
        $admins = Admin::with('user')->paginate(10);
        return view('admin.admins.index', compact('admins'));
    }

    /**
     * Show the form for creating a new admin.
     */
    public function create()
    {
        return view('admin.admins.create');
    }

    /**
     * Store a newly created admin in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
        ]);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => 'admin',
        ]);

        // Create admin profile
        Admin::create([
            'user_id' => $user->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
        ]);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin created successfully.');
    }

    /**
     * Display the specified admin.
     */
    public function show(Admin $admin)
    {
        $admin->load('user');
        return view('admin.admins.show', compact('admin'));
    }

    /**
     * Show the form for editing the specified admin.
     */
    public function edit(Admin $admin)
    {
        $admin->load('user');
        return view('admin.admins.edit', compact('admin'));
    }

    /**
     * Update the specified admin in storage.
     */
    public function update(Request $request, Admin $admin)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($admin->user_id)],
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
        ]);

        // Update user
        $admin->user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Update password if provided
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8|confirmed',
            ]);

            $admin->user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        // Update admin profile
        $admin->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
        ]);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin updated successfully.');
    }

    /**
     * Remove the specified admin from storage.
     */
    public function destroy(Admin $admin)
    {
        // Delete user (will cascade delete admin due to foreign key)
        $admin->user->delete();

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin deleted successfully.');
    }

    /**
     * Show reports page
     */
    public function reports()
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            // SuperAdmin sees all employees with leaves
            $employees_with_leaves = Employee::with(['user', 'leaves' => function ($query) {
                $query->approved()->currentYear();
            }, 'creator'])->get();

            $leave_statistics = [
                'total_approved_leaves' => Leave::approved()->currentYear()->count(),
                'total_pending_leaves' => Leave::pending()->count(),
                'total_leave_days' => Leave::approved()->currentYear()->get()->sum('total_days'),
            ];
        } else {
            // Regular admin sees only their employees
            $employeeIds = $user->createdEmployees()->pluck('id');

            $employees_with_leaves = Employee::with(['user', 'leaves' => function ($query) {
                $query->approved()->currentYear();
            }, 'creator'])
                ->whereIn('id', $employeeIds)
                ->get();

            $leave_statistics = [
                'total_approved_leaves' => Leave::approved()->currentYear()->whereIn('employee_id', $employeeIds)->count(),
                'total_pending_leaves' => Leave::pending()->whereIn('employee_id', $employeeIds)->count(),
                'total_leave_days' => Leave::approved()->currentYear()->whereIn('employee_id', $employeeIds)->get()->sum('total_days'),
            ];
        }

        return view('admin.reports', compact('employees_with_leaves', 'leave_statistics'));
    }

    /**
     * Show admin profile page
     */
    public function profile()
    {
        $admin = auth()->user()->admin;

        if (!$admin) {
            // Jika user admin tidak memiliki record di table admins
            return redirect()->route('admin.dashboard')
                ->with('error', 'Admin profile not found. Please contact system administrator.');
        }

        return view('admin.profile', compact('admin'));
    }

    /**
     * Update admin profile
     */
    public function updateProfile(Request $request)
    {
        $admin = auth()->user()->admin;

        if (!$admin) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Admin profile not found.');
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
        ]);

        // Update admin profile
        $admin->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
        ]);

        // Update user name to match admin name
        $admin->user->update([
            'name' => $request->first_name . ' ' . $request->last_name,
        ]);

        return redirect()->route('admin.profile')
            ->with('success', 'Profile updated successfully.');
    }
}
