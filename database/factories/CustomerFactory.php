<?php

namespace Database\Factories;

use App\Enums\CustomerPlatform;
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
            'contact_detail' => fake()->optional(0.8)->randomElement([
                fake()->safeEmail(),
                '@'.fake()->userName(),
            ]),
            'platform' => fake()->optional(0.8)->randomElement(CustomerPlatform::cases()),
            'phone' => fake()->optional(0.6)->phoneNumber(),
            'address' => fake()->optional(0.5)->address(),
        ];
    }
}
