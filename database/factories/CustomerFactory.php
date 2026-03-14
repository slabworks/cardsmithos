<?php

namespace Database\Factories;

use App\Enums\CustomerStatus;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
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
            'name' => fake()->name(),
            'status' => fake()->randomElement(CustomerStatus::cases()),
            'email' => fake()->optional(0.8)->safeEmail(),
            'phone' => fake()->optional(0.6)->phoneNumber(),
            'address' => fake()->optional(0.5)->address(),
            'notes' => fake()->optional(0.3)->paragraph(),
            'referral_source' => fake()->optional(0.4)->word(),
            'waiver_agreed' => fake()->optional(0.3)->boolean(),
            'waiver_agreed_at' => fake()->optional(0.3)->dateTimeBetween('-1 year'),
        ];
    }
}
