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
            'email' => 'superadmin@company.com',
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
            'email' => 'admin@company.com',
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

        // Create 5 Employees with realistic data
        $employees = [];

        $employeeData = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@company.com',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => '+62 812 3456 7890',
                'address' => 'Jl. Sudirman No. 123, Jakarta Selatan, DKI Jakarta',
                'gender' => 'male',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@company.com',
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'phone' => '+62 813 4567 8901',
                'address' => 'Jl. Thamrin No. 456, Jakarta Pusat, DKI Jakarta',
                'gender' => 'female',
            ],
            [
                'name' => 'Mike Johnson',
                'email' => 'mike.johnson@company.com',
                'first_name' => 'Mike',
                'last_name' => 'Johnson',
                'phone' => '+62 814 5678 9012',
                'address' => 'Jl. Gatot Subroto No. 789, Jakarta Selatan, DKI Jakarta',
                'gender' => 'male',
            ],
            [
                'name' => 'Sarah Wilson',
                'email' => 'sarah.wilson@company.com',
                'first_name' => 'Sarah',
                'last_name' => 'Wilson',
                'phone' => '+62 815 6789 0123',
                'address' => 'Jl. Kuningan Raya No. 321, Jakarta Selatan, DKI Jakarta',
                'gender' => 'female',
            ],
            [
                'name' => 'David Brown',
                'email' => 'david.brown@company.com',
                'first_name' => 'David',
                'last_name' => 'Brown',
                'phone' => '+62 816 7890 1234',
                'address' => 'Jl. Senayan No. 654, Jakarta Pusat, DKI Jakarta',
                'gender' => 'male',
            ],
        ];

        foreach ($employeeData as $data) {
            $user = User::factory()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'user_type' => 'employee',
            ]);

            $employee = Employee::factory()->create([
                'user_id' => $user->id,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'gender' => $data['gender'],
            ]);

            $employees[] = $employee;
        }

        // Create sample leave requests for employees
        $approvers = [$superAdminUser, $adminUser]; // Both can approve leaves

        foreach ($employees as $employee) {
            // Create 2-4 leave requests per employee
            $leaveCount = rand(2, 4);

            for ($i = 0; $i < $leaveCount; $i++) {
                $status = ['pending', 'approved', 'approved', 'rejected'][rand(0, 3)]; // More approved than others

                $leave = Leave::factory()
                    ->forEmployee($employee)
                    ->create();

                // Set status based on random selection
                if ($status === 'approved') {
                    $approver = $approvers[array_rand($approvers)];
                    $leave->update([
                        'status' => 'approved',
                        'approved_by' => $approver->id,
                        'approved_at' => now()->subDays(rand(1, 30)),
                        'admin_notes' => collect([
                            'Approved as requested',
                            'Enjoy your time off',
                            'Leave approved',
                            null
                        ])->random(),
                    ]);
                } elseif ($status === 'rejected') {
                    $approver = $approvers[array_rand($approvers)];
                    $leave->update([
                        'status' => 'rejected',
                        'approved_by' => $approver->id,
                        'approved_at' => now()->subDays(rand(1, 15)),
                        'admin_notes' => collect([
                            'Insufficient staff coverage during requested period',
                            'Request conflicts with project deadline',
                            'Please resubmit with different dates',
                        ])->random(),
                    ]);
                }
                // Pending leaves remain as is (no approver or notes)
            }
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('');
        $this->command->info('🔑 LOGIN CREDENTIALS:');
        $this->command->info('SuperAdmin: superadmin@company.com / password');
        $this->command->info('Admin: admin@company.com / password');
        $this->command->info('Employees: john.doe@company.com, jane.smith@company.com, etc. / password');
        $this->command->info('');
        $this->command->info('👥 ROLE PERMISSIONS:');
        $this->command->info('SuperAdmin: Full access (manage admins, employees, leaves, reports)');
        $this->command->info('Admin: Limited access (manage employees, leaves, reports - NO admin management)');
        $this->command->info('Employee: Self-service (own leaves and profile only)');
    }
}
