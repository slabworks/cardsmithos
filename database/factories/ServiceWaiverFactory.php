<?php

namespace Database\Factories;

use App\Models\ServiceWaiver;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceWaiver>
 */
class ServiceWaiverFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'expires_at' => now()->addDays(30),
        ];
    }

    public function signed(): static
    {
        return $this->state(fn (array $attributes) => [
            'signed_at' => now(),
            'signer_name' => fake()->name(),
            'signer_email' => fake()->email(),
        ]);
    }
}
