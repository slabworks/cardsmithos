<?php

namespace Database\Factories;

use App\Enums\ExpenseCategory;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'description' => fake()->sentence(3),
            'amount' => fake()->randomFloat(2, 5, 500),
            'category' => fake()->randomElement(ExpenseCategory::cases()),
            'occurred_at' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'notes' => fake()->optional(0.3)->paragraph(),
        ];
    }
}
