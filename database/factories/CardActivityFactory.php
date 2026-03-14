<?php

namespace Database\Factories;

use App\Enums\CardActivityType;
use App\Models\Card;
use App\Models\CardActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CardActivity>
 */
class CardActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'card_id' => Card::factory(),
            'type' => fake()->randomElement(CardActivityType::cases()),
            'title' => fake()->sentence(4),
            'description' => fake()->optional(0.7)->paragraph(),
            'occurred_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
