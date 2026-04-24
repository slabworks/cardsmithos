<?php

namespace Database\Factories;

use App\Models\BusinessStatistic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BusinessStatistic>
 */
class BusinessStatisticFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'user_id' => User::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 9999),
            'source' => 'custom',
            'category' => fake()->randomElement(['sales', 'leads', 'marketing', 'finance', 'ops']),
            'period' => fake()->randomElement(['daily', 'weekly', 'monthly', 'yearly', 'custom']),
            'value_type' => fake()->randomElement(['number', 'currency', 'percentage']),
            'input_method' => 'manual',
            'sort_order' => 0,
        ];
    }
}
