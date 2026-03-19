<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Shipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shipment>
 */
class ShipmentFactory extends Factory
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
            'amount' => fake()->randomFloat(2, 5, 75),
            'shipped_at' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'reference' => fake()->optional(0.4)->regexify('[A-Z0-9]{6,12}'),
            'tracking_number' => fake()->optional(0.7)->regexify('[A-Z0-9]{8,18}'),
        ];
    }
}
