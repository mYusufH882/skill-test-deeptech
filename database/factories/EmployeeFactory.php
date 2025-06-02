<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'gender' => fake()->randomElement(['male', 'female']),
        ];
    }

    /**
     * Create employee with user relationship
     */
    public function withUser(): static
    {
        return $this->state(function (array $attributes) {
            $user = User::factory()->employee()->create([
                'name' => $attributes['first_name'] . ' ' . $attributes['last_name'],
            ]);

            return [
                'user_id' => $user->id,
            ];
        });
    }
}
