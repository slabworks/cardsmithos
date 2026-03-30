<?php

namespace Database\Factories;

use App\Models\GmailAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GmailAccount>
 */
class GmailAccountFactory extends Factory
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
            'google_id' => fake()->numerify('####################'),
            'email' => fake()->safeEmail(),
            'access_token' => fake()->sha256(),
            'refresh_token' => fake()->sha256(),
            'token_expires_at' => now()->addHour(),
            'history_id' => fake()->numerify('######'),
            'last_synced_at' => now(),
        ];
    }
}
