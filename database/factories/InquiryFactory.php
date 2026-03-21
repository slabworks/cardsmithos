<?php

namespace Database\Factories;

use App\Enums\CommunicationMethod;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inquiry>
 */
class InquiryFactory extends Factory
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
            'customer_id' => null,
            'contact_username' => fake()->userName(),
            'communication_method' => fake()->randomElement(CommunicationMethod::cases()),
            'inquired_at' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'converted' => fake()->boolean(25),
            'notes' => fake()->optional(0.4)->paragraph(),
        ];
    }
}
