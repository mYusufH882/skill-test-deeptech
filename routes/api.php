<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\EmployeeController;
use App\Http\Controllers\Api\V1\LeaveController;
use App\Http\Controllers\Api\V1\AdminLeaveController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API Version 1
Route::prefix('v1')->group(function () {

    // Public Authentication Routes
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']); // Optional for employee self-registration
    });

    // Protected Routes (require authentication)
    Route::middleware(['auth:sanctum'])->group(function () {

        // Authentication Routes
        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
            Route::put('me', [AuthController::class, 'updateProfile']);
        });

        // SuperAdmin Only Routes
        Route::middleware(['api.role:superadmin'])->group(function () {
            Route::apiResource('admins', AdminController::class);
        });

        // Admin Routes (SuperAdmin + Admin)
        Route::middleware(['api.role:admin'])->group(function () {
            // Employee Management
            Route::apiResource('employees', EmployeeController::class);

            // Admin Leave Management
            Route::prefix('admin/leaves')->name('admin.leaves.')->group(function () {
                Route::get('/', [AdminLeaveController::class, 'index'])->name('index');
                Route::get('{leave}', [AdminLeaveController::class, 'show'])->name('show');
                Route::put('{leave}/approve', [AdminLeaveController::class, 'approve'])->name('approve');
                Route::put('{leave}/reject', [AdminLeaveController::class, 'reject'])->name('reject');
            });

            // Reports
            Route::prefix('reports')->group(function () {
                Route::get('dashboard', [ReportController::class, 'dashboard']);
                Route::get('employees', [ReportController::class, 'employees']);
                Route::get('leaves', [ReportController::class, 'leaves']);
            });
        });

        // Employee Routes
        Route::middleware(['api.role:employee'])->group(function () {
            // Employee Leave Management
            Route::apiResource('leaves', LeaveController::class)->except(['destroy']);

            // Employee Profile & Dashboard
            Route::get('profile', [ProfileController::class, 'show']);
            Route::put('profile', [ProfileController::class, 'update']);
            Route::get('dashboard', [ProfileController::class, 'dashboard']);
        });

        // Mixed Role Routes (Admin can view employee leaves, Employee can view own)
        Route::get('leaves/{leave}', [LeaveController::class, 'show'])
            ->middleware(['api.role:employee|admin']);
    });
});
