<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    /**
     * Employee Dashboard
     */
    public function dashboard()
    {
        $employee = auth()->user()->employee;

        $stats = [
            'total_leave_days_used' => $employee->getTotalLeaveDaysThisYear(),
            'remaining_leave_days' => $employee->getRemainingLeaveDays(),
            'pending_requests' => $employee->leaves()->pending()->count(),
            'approved_requests' => $employee->leaves()->approved()->currentYear()->count(),
        ];
        // dd($stats);

        $recent_leaves = $employee->leaves()
            ->latest()
            ->take(5)
            ->get();

        return view('employee.dashboard', compact('stats', 'recent_leaves'));
    }

    /**
     * Display a listing of employees (Admin view)
     */
    public function index()
    {
        $user = auth()->user();

        // Filter employees based on user role
        if ($user->isSuperAdmin()) {
            // SuperAdmin sees all employees
            $employees = Employee::with(['user', 'creator'])->paginate(10);
        } else {
            // Regular admin sees only employees they created
            $employees = Employee::with(['user', 'creator'])
                ->where('created_by', $user->id)
                ->paginate(10);
        }

        return view('admin.employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new employee (Admin view)
     */
    public function create()
    {
        return view('admin.employees.create');
    }

    /**
     * Store a newly created employee (Admin view)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'gender' => 'required|in:male,female',
        ]);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => 'employee',
        ]);

        // Create employee profile with created_by
        Employee::create([
            'user_id' => $user->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'address' => $request->address,
            'gender' => $request->gender,
            'created_by' => auth()->id(), // SET CREATOR
        ]);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee created successfully.');
    }

    /**
     * Display the specified employee
     */
    public function show(Employee $employee)
    {
        $user = auth()->user();

        // Check access permission
        if (!$user->isSuperAdmin() && $employee->created_by !== $user->id) {
            abort(403, 'You can only view employees you created.');
        }

        $employee->load(['user', 'leaves', 'creator']);

        $leave_stats = [
            'total_used' => $employee->getTotalLeaveDaysThisYear(),
            'remaining' => $employee->getRemainingLeaveDays(),
            'this_year_leaves' => $employee->leaves()->currentYear()->get(),
        ];

        return view('admin.employees.show', compact('employee', 'leave_stats'));
    }

    /**
     * Show the form for editing the specified employee
     */
    public function edit(Employee $employee)
    {
        $user = auth()->user();

        // Check access permission
        if (!$user->isSuperAdmin() && $employee->created_by !== $user->id) {
            abort(403, 'You can only edit employees you created.');
        }

        $employee->load(['user', 'creator']);
        return view('admin.employees.edit', compact('employee'));
    }

    /**
     * Update the specified employee
     */
    public function update(Request $request, Employee $employee)
    {
        $user = auth()->user();

        // Check access permission
        if (!$user->isSuperAdmin() && $employee->created_by !== $user->id) {
            abort(403, 'You can only update employees you created.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($employee->user_id)],
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'gender' => 'required|in:male,female',
        ]);

        // Update user
        $employee->user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Update password if provided
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8|confirmed',
            ]);

            $employee->user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        // Update employee profile (created_by tidak berubah)
        $employee->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'address' => $request->address,
            'gender' => $request->gender,
            // created_by TIDAK diupdate, tetap milik creator asli
        ]);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    /**
     * Remove the specified employee
     */
    public function destroy(Employee $employee)
    {
        $user = auth()->user();

        // Check access permission
        if (!$user->isSuperAdmin() && $employee->created_by !== $user->id) {
            abort(403, 'You can only delete employees you created.');
        }

        // Delete user (will cascade delete employee due to foreign key)
        $employee->user->delete();

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee deleted successfully.');
    }

    /**
     * Show employee profile (Employee view)
     */
    public function profile()
    {
        $employee = auth()->user()->employee;
        return view('employee.profile', compact('employee'));
    }

    /**
     * Update employee profile (Employee view)
     */
    public function updateProfile(Request $request)
    {
        $employee = auth()->user()->employee;

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
        ]);

        $employee->update($request->only([
            'first_name',
            'last_name',
            'phone',
            'address'
        ]));

        return redirect()->route('employee.profile')
            ->with('success', 'Profile updated successfully.');
    }
}
