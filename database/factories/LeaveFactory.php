<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Leave>
 */
class LeaveFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('2024-01-01', '2024-12-31');
        $endDate = (clone $startDate)->modify('+' . fake()->numberBetween(1, 3) . ' days');

        return [
            'employee_id' => Employee::factory()->withUser(),
            'reason' => fake()->randomElement([
                'Annual vacation with family',
                'Medical appointment and recovery',
                'Personal matters to attend',
                'Wedding ceremony attendance',
                'Emergency family situation',
                'Planned medical procedure',
                'Religious holiday observance',
                'Moving to new residence',
                'Child care responsibilities',
                'Mental health break',
            ]),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
        ];
    }

    /**
     * Create pending leave
     */
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
            'admin_notes' => null,
        ]);
    }

    /**
     * Create approved leave
     */
    public function approved(): static
    {
        return $this->state(function (array $attributes) {
            $admin = User::where('user_type', 'admin')->first();

            return [
                'status' => 'approved',
                'approved_by' => $admin ? $admin->id : null,
                'approved_at' => fake()->dateTimeBetween('-30 days', 'now'),
                'admin_notes' => fake()->optional()->randomElement([
                    'Approved as requested',
                    'Leave approved with conditions',
                    'Enjoy your time off',
                    null
                ]),
            ];
        });
    }

    /**
     * Create rejected leave
     */
    public function rejected(): static
    {
        return $this->state(function (array $attributes) {
            $admin = User::where('user_type', 'admin')->first();

            return [
                'status' => 'rejected',
                'approved_by' => $admin ? $admin->id : null,
                'approved_at' => fake()->dateTimeBetween('-30 days', 'now'),
                'admin_notes' => fake()->randomElement([
                    'Insufficient staff coverage during requested period',
                    'Request conflicts with project deadline',
                    'Please resubmit with different dates',
                    'Maximum leave quota exceeded for this month',
                ]),
            ];
        });
    }

    /**
     * Create leave for specific employee
     */
    public function forEmployee(Employee $employee): static
    {
        return $this->state(fn(array $attributes) => [
            'employee_id' => $employee->id,
        ]);
    }
}
