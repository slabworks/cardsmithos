<?php

namespace Database\Factories;

use App\Enums\CardCondition;
use App\Enums\CardStatus;
use App\Models\Card;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Card>
 */
class CardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'name' => fake()->words(3, true),
            'work_done' => fake()->optional(0.5)->paragraph(),
            'status' => fake()->randomElement(CardStatus::cases()),
            'condition_before' => fake()->optional(0.8)->randomElement(CardCondition::cases()),
            'condition_after' => null,
            'restoration_hours' => fake()->optional(0.6)->randomFloat(2, 0.5, 20),
            'estimated_fee' => null,
            'photos' => null,
        ];
    }
}
