<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Admin;
use App\Models\Employee;
use App\Models\Leave;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create 1 SuperAdmin
        $superAdminUser = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@mail.com',
            'password' => Hash::make('password'),
            'user_type' => 'superadmin',
        ]);

        Admin::factory()->create([
            'user_id' => $superAdminUser->id,
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'birth_date' => '1980-01-15',
            'gender' => 'male',
        ]);

        // Create 1 Regular Admin
        $adminUser = User::factory()->create([
            'name' => 'HR Admin',
            'email' => 'admin@mail.com',
            'password' => Hash::make('password'),
            'user_type' => 'admin',
        ]);

        Admin::factory()->create([
            'user_id' => $adminUser->id,
            'first_name' => 'HR',
            'last_name' => 'Admin',
            'birth_date' => '1985-06-20',
            'gender' => 'female',
        ]);

        // Create 2 Employees with realistic data
        $employees = [];

        // Employee 1: John Doe
        $johnUser = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john.doe@mail.com',
            'password' => Hash::make('password'),
            'user_type' => 'employee',
        ]);

        $johnEmployee = Employee::factory()->create([
            'user_id' => $johnUser->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+62 812 3456 7890',
            'address' => 'Jl. Sudirman No. 123, Jakarta Selatan, DKI Jakarta',
            'gender' => 'male',
            'created_by' => $adminUser->id, // Created by HR Admin
        ]);

        $employees[] = $johnEmployee;

        // Employee 2: Jane Smith
        $janeUser = User::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane.smith@mail.com',
            'password' => Hash::make('password'),
            'user_type' => 'employee',
        ]);

        $janeEmployee = Employee::factory()->create([
            'user_id' => $janeUser->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'phone' => '+62 813 4567 8901',
            'address' => 'Jl. Thamrin No. 456, Jakarta Pusat, DKI Jakarta',
            'gender' => 'female',
            'created_by' => $adminUser->id, // Created by HR Admin
        ]);

        $employees[] = $janeEmployee;

        // Create sample leave requests for employees
        $approvers = [$superAdminUser, $adminUser]; // Both can approve leaves

        // John Doe's Leave History (5 leaves) - Updated to 2025
        $johnLeaves = [
            [
                'reason' => 'Annual vacation with family',
                'start_date' => '2025-01-15',
                'end_date' => '2025-01-17', // 3 days
                'status' => 'approved',
                'admin_notes' => 'Approved as requested. Enjoy your vacation!',
            ],
            [
                'reason' => 'Medical appointment and recovery',
                'start_date' => '2025-02-10',
                'end_date' => '2025-02-10', // 1 day
                'status' => 'approved',
                'admin_notes' => 'Medical leave approved. Get well soon!',
            ],
            [
                'reason' => 'Wedding ceremony attendance',
                'start_date' => '2025-03-20',
                'end_date' => '2025-03-21', // 2 days
                'status' => 'approved',
                'admin_notes' => 'Congratulations! Enjoy the celebration.',
            ],
            [
                'reason' => 'Emergency family situation',
                'start_date' => '2025-04-08',
                'end_date' => '2025-04-08', // 1 day
                'status' => 'rejected',
                'admin_notes' => 'Insufficient staff coverage. Please reschedule if possible.',
            ],
            [
                'reason' => 'Personal matters to attend',
                'start_date' => '2025-07-23',
                'end_date' => '2025-07-24', // 2 days
                'status' => 'pending',
                'admin_notes' => null,
            ],
        ];

        foreach ($johnLeaves as $leaveData) {
            $approver = $approvers[array_rand($approvers)];

            $leave = Leave::create([
                'employee_id' => $johnEmployee->id,
                'reason' => $leaveData['reason'],
                'start_date' => $leaveData['start_date'],
                'end_date' => $leaveData['end_date'],
                'status' => $leaveData['status'],
                'admin_notes' => $leaveData['admin_notes'],
                'approved_by' => in_array($leaveData['status'], ['approved', 'rejected']) ? $approver->id : null,
                'approved_at' => in_array($leaveData['status'], ['approved', 'rejected']) ? now()->subDays(rand(1, 30)) : null,
            ]);
        }

        // Jane Smith's Leave History (4 leaves) - Updated to 2025
        $janeLeaves = [
            [
                'reason' => 'Planned medical procedure',
                'start_date' => '2025-01-05',
                'end_date' => '2025-01-07', // 3 days
                'status' => 'approved',
                'admin_notes' => 'Medical procedure approved. Take care!',
            ],
            [
                'reason' => 'Child care responsibilities',
                'start_date' => '2025-02-12',
                'end_date' => '2025-02-12', // 1 day
                'status' => 'approved',
                'admin_notes' => 'Family care leave approved.',
            ],
            [
                'reason' => 'Religious holiday observance',
                'start_date' => '2025-04-15',
                'end_date' => '2025-04-16', // 2 days
                'status' => 'approved',
                'admin_notes' => 'Religious observance approved.',
            ],
            [
                'reason' => 'Mental health break and relaxation',
                'start_date' => '2025-08-10',
                'end_date' => '2025-08-12', // 3 days
                'status' => 'pending',
                'admin_notes' => null,
            ],
        ];

        foreach ($janeLeaves as $leaveData) {
            $approver = $approvers[array_rand($approvers)];

            $leave = Leave::create([
                'employee_id' => $janeEmployee->id,
                'reason' => $leaveData['reason'],
                'start_date' => $leaveData['start_date'],
                'end_date' => $leaveData['end_date'],
                'status' => $leaveData['status'],
                'admin_notes' => $leaveData['admin_notes'],
                'approved_by' => in_array($leaveData['status'], ['approved', 'rejected']) ? $approver->id : null,
                'approved_at' => in_array($leaveData['status'], ['approved', 'rejected']) ? now()->subDays(rand(1, 30)) : null,
            ]);
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('');
        $this->command->info('🔑 LOGIN CREDENTIALS:');
        $this->command->info('SuperAdmin: superadmin@mail.com / password');
        $this->command->info('Admin: admin@mail.com / password');
        $this->command->info('Employee 1: john.doe@mail.com / password');
        $this->command->info('Employee 2: jane.smith@mail.com / password');
        $this->command->info('');
        $this->command->info('👥 ROLE PERMISSIONS:');
        $this->command->info('SuperAdmin: Full access (manage admins, employees, leaves, reports)');
        $this->command->info('Admin: Limited access (manage employees, leaves, reports - NO admin management)');
        $this->command->info('Employee: Self-service (own leaves and profile only)');
        $this->command->info('');
        $this->command->info('📊 EMPLOYEE LEAVE SUMMARY 2025:');
        $this->command->info('John Doe: 6 days used (3+1+2 approved), 6 days remaining, 1 pending, 1 rejected');
        $this->command->info('Jane Smith: 6 days used (3+1+2 approved), 6 days remaining, 1 pending');
    }
}
