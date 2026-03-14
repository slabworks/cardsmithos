<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
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
            'amount' => fake()->randomFloat(2, 10, 500),
            'paid_at' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'method' => fake()->randomElement(PaymentMethod::cases()),
            'reference' => fake()->optional(0.4)->regexify('[A-Z0-9]{6,12}'),
        ];
    }
}
