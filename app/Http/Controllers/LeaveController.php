<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeaveController extends Controller
{
    /**
     * Display employee's leaves (Employee view)
     */
    public function index()
    {
        $employee = auth()->user()->employee;

        // Check if user has employee profile
        if (!$employee) {
            abort(403, 'Employee profile not found. Please contact administrator.');
        }

        $leaves = $employee->leaves()->latest()->paginate(10);

        return view('employee.leaves.index', compact('leaves'));
    }

    /**
     * Show the form for creating a new leave (Employee view)
     */
    public function create()
    {
        $employee = auth()->user()->employee;

        // Check if user has employee profile
        if (!$employee) {
            abort(403, 'Employee profile not found. Please contact administrator.');
        }

        $stats = [
            'remaining_days' => $employee->getRemainingLeaveDays(),
            'used_days' => $employee->getTotalLeaveDaysThisYear(),
        ];

        return view('employee.leaves.create', compact('stats'));
    }

    /**
     * Store a newly created leave (Employee view)
     */
    public function store(Request $request)
    {
        $employee = auth()->user()->employee;

        // Check if user has employee profile
        if (!$employee) {
            return redirect()->back()->withErrors(['error' => 'Employee profile not found. Please contact administrator.']);
        }

        $request->validate([
            'reason' => 'required|string|max:1000',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        // Business Rule 1: Check if employee has enough remaining leave days
        if ($employee->getRemainingLeaveDays() < $totalDays) {
            return back()->withErrors([
                'start_date' => 'Insufficient leave days. You have ' . $employee->getRemainingLeaveDays() . ' days remaining.'
            ]);
        }

        // Business Rule 2: Check if employee already has leave in the same month
        if (!$employee->canTakeLeaveInMonth($startDate->year, $startDate->month)) {
            return back()->withErrors([
                'start_date' => 'You already have approved leave in ' . $startDate->format('F Y') . '. Only one leave per month is allowed.'
            ]);
        }

        // Check if leave spans multiple months (additional validation)
        if ($startDate->month !== $endDate->month) {
            if (!$employee->canTakeLeaveInMonth($endDate->year, $endDate->month)) {
                return back()->withErrors([
                    'end_date' => 'You already have approved leave in ' . $endDate->format('F Y') . '. Only one leave per month is allowed.'
                ]);
            }
        }

        Leave::create([
            'employee_id' => $employee->id,
            'reason' => $request->reason,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'pending',
        ]);

        return redirect()->route('employee.leaves.index')
            ->with('success', 'Leave request submitted successfully.');
    }

    /**
     * Display the specified leave
     */
    public function show(Leave $leave)
    {
        // Load the employee relationship to avoid null errors
        $leave->load(['employee.user', 'approver']);

        // Check if leave has employee (data integrity check)
        if (!$leave->employee) {
            abort(404, 'Leave request data is invalid. Employee not found.');
        }

        // Check authorization for employees
        if (auth()->user()->isEmployee()) {
            // Check if user has employee profile
            $currentEmployee = auth()->user()->employee;
            if (!$currentEmployee) {
                abort(403, 'Employee profile not found. Please contact administrator.');
            }

            // Check if this leave belongs to current employee
            if ($leave->employee_id !== $currentEmployee->id) {
                abort(403, 'You can only view your own leave requests.');
            }
        }

        return view('employee.leaves.show', compact('leave'));
    }

    /**
     * Show the form for editing the specified leave (Employee view)
     */
    public function edit(Leave $leave)
    {
        // Load the employee relationship
        $leave->load('employee.user');

        // Check if leave has employee
        if (!$leave->employee) {
            abort(404, 'Leave request data is invalid. Employee not found.');
        }

        // Check if user has employee profile
        $currentEmployee = auth()->user()->employee;
        if (!$currentEmployee) {
            abort(403, 'Employee profile not found. Please contact administrator.');
        }

        // Check authorization
        if ($leave->employee_id !== $currentEmployee->id) {
            abort(403, 'You can only edit your own leave requests.');
        }

        // Only allow editing pending leaves
        if (!$leave->isPending()) {
            return back()->with('error', 'You can only edit pending leave requests.');
        }

        return view('employee.leaves.edit', compact('leave'));
    }

    /**
     * Update the specified leave (Employee view)
     */
    public function update(Request $request, Leave $leave)
    {
        // Load the employee relationship
        $leave->load('employee');

        // Check if leave has employee
        if (!$leave->employee) {
            return back()->withErrors(['error' => 'Leave request data is invalid. Employee not found.']);
        }

        // Check if user has employee profile
        $currentEmployee = auth()->user()->employee;
        if (!$currentEmployee) {
            return back()->withErrors(['error' => 'Employee profile not found. Please contact administrator.']);
        }

        // Check authorization
        if ($leave->employee_id !== $currentEmployee->id) {
            abort(403, 'You can only edit your own leave requests.');
        }

        // Only allow editing pending leaves
        if (!$leave->isPending()) {
            return back()->with('error', 'You can only edit pending leave requests.');
        }

        $employee = $leave->employee;

        $request->validate([
            'reason' => 'required|string|max:1000',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        // Calculate remaining days excluding current leave
        $currentLeaveDays = $leave->total_days;
        $availableDays = $employee->getRemainingLeaveDays() + $currentLeaveDays;

        if ($availableDays < $totalDays) {
            return back()->withErrors([
                'start_date' => 'Insufficient leave days. You have ' . $availableDays . ' days available.'
            ]);
        }

        $leave->update([
            'reason' => $request->reason,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return redirect()->route('employee.leaves.index')
            ->with('success', 'Leave request updated successfully.');
    }

    /**
     * Display leaves for admin management (Admin view)
     */
    public function adminIndex()
    {
        $leaves = Leave::with(['employee.user'])
            ->latest()
            ->paginate(15);

        return view('admin.leaves.index', compact('leaves'));
    }

    /**
     * Approve leave (Admin action)
     */
    public function approve(Request $request, Leave $leave)
    {
        // Load employee relationship
        $leave->load('employee');

        // Check if leave has employee
        if (!$leave->employee) {
            return back()->withErrors(['error' => 'Leave request data is invalid. Employee not found.']);
        }

        $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $leave->approve(auth()->user(), $request->admin_notes);

        return back()->with('success', 'Leave request approved successfully.');
    }

    /**
     * Reject leave (Admin action)
     */
    public function reject(Request $request, Leave $leave)
    {
        // Load employee relationship
        $leave->load('employee');

        // Check if leave has employee
        if (!$leave->employee) {
            return back()->withErrors(['error' => 'Leave request data is invalid. Employee not found.']);
        }

        $request->validate([
            'admin_notes' => 'required|string|max:500',
        ]);

        $leave->reject(auth()->user(), $request->admin_notes);

        return back()->with('success', 'Leave request rejected.');
    }
}
