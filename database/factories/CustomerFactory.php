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
            'notes' => fake()->optional(0.3)->randomElement([
                'Repeat customer, prefers bubble mailer. Has a large vintage Pokemon collection.',
                'Bulk order — 20+ cards for grading prep. Wants PSA submission.',
                'First-time customer. Found us through Instagram. Sent in 3 base set holos.',
                'Collector focused on Yu-Gi-Oh! 1st editions. Very particular about handling.',
                'Sports card collector — mostly vintage baseball. Ships via Priority Mail only.',
                'Wants cards back ASAP for eBay listings. Paid for rush turnaround.',
                'Long-time customer, always sends Pokemon gold stars. Trusts our process.',
                'Inquired about bulk discount for 50+ cards. Mostly modern ultras.',
                'Prefers detailed before/after photos for each card. Posts results on Reddit.',
                'Local collector — drops off and picks up in person. No shipping needed.',
            ]),
            'referral_source' => fake()->optional(0.4)->randomElement([
                'Instagram', 'Discord', 'eBay', 'Local card show', 'Facebook group',
                'Reddit', 'Friend referral', 'YouTube', 'TikTok', 'Google search',
            ]),
            'waiver_agreed' => fake()->optional(0.3)->boolean(),
            'waiver_agreed_at' => fake()->optional(0.3)->dateTimeBetween('-1 year'),
        ];
    }
}
