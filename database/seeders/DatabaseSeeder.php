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
        // Create 1 Admin
        $adminUser = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@company.com',
            'password' => Hash::make('password'),
            'user_type' => 'admin',
        ]);

        Admin::factory()->create([
            'user_id' => $adminUser->id,
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'birth_date' => '1985-06-15',
            'gender' => 'male',
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
                    $leave->update([
                        'status' => 'approved',
                        'approved_by' => $adminUser->id,
                        'approved_at' => now()->subDays(rand(1, 30)),
                        'admin_notes' => collect([
                            'Approved as requested',
                            'Enjoy your time off',
                            'Leave approved',
                            null
                        ])->random(),
                    ]);
                } elseif ($status === 'rejected') {
                    $leave->update([
                        'status' => 'rejected',
                        'approved_by' => $adminUser->id,
                        'approved_at' => now()->subDays(rand(1, 15)),
                        'admin_notes' => collect([
                            'Insufficient staff coverage during requested period',
                            'Request conflicts with project deadline',
                            'Please resubmit with different dates',
                        ])->random(),
                    ]);
                }
            }
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin Login: admin@company.com / password');
        $this->command->info('Employee Logins: john.doe@company.com, jane.smith@company.com, etc. / password');
    }
}
