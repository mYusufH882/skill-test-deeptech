<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Dashboard route - redirect based on user type
Route::get('/dashboard', function () {
    $user = auth()->user();

    // Debug log
    \Log::info('Dashboard redirect:', [
        'email' => $user->email,
        'user_type' => $user->user_type,
        'isAdmin' => $user->isAdmin(),
        'isSuperAdmin' => $user->isSuperAdmin()
    ]);

    // Explicit redirect based on user_type
    if ($user->user_type === 'superadmin' || $user->user_type === 'admin') {
        return redirect()->route('admin.dashboard');
    } else {
        return redirect()->route('employee.dashboard');
    }
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__ . '/auth.php';

// Admin routes (accessible by both SuperAdmin and Admin) - MUST BE FIRST
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Employee management - Both SuperAdmin and Admin can manage employees
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

    // Leave management - Both SuperAdmin and Admin can manage leaves
    Route::get('/leaves', [LeaveController::class, 'adminIndex'])->name('leaves.index');
    Route::put('/leaves/{leave}/approve', [LeaveController::class, 'approve'])->name('leaves.approve');
    Route::put('/leaves/{leave}/reject', [LeaveController::class, 'reject'])->name('leaves.reject');

    // Reports - Both SuperAdmin and Admin can view reports
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
});

// SuperAdmin routes - MUST BE AFTER admin routes (more specific)
Route::middleware(['auth', 'role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin management - Only SuperAdmin can manage admins
    Route::resource('admins', AdminController::class);
});

// Employee routes
Route::middleware(['auth', 'role:employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [EmployeeController::class, 'dashboard'])->name('dashboard');

    // Employee profile
    Route::get('/profile', [EmployeeController::class, 'profile'])->name('profile');
    Route::put('/profile', [EmployeeController::class, 'updateProfile'])->name('profile.update');

    // Leave requests
    Route::resource('leaves', LeaveController::class)->except(['destroy']);
});

// Shared authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
