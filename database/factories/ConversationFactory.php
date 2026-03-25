<?php

namespace Database\Factories;

use App\Enums\ConversationStatus;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Conversation>
 */
class ConversationFactory extends Factory
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
            'access_token' => Str::random(64),
            'guest_name' => fake()->name(),
            'guest_email' => fake()->safeEmail(),
            'subject' => fake()->optional(0.6)->randomElement([
                'Question about Pokemon card restoration',
                'Bulk order pricing for Yu-Gi-Oh! cards',
                'Grading prep for 1st Edition Base Set',
                'Turnaround time for sports card cleaning',
                'Pressing service for vintage holos',
                'Shipping instructions for international order',
                'Before/after photos request',
                'Rush order for tournament cards',
                'CGC submission service inquiry',
                'Quote for heavily played card restoration',
            ]),
            'status' => fake()->randomElement(ConversationStatus::cases()),
            'last_message_at' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }
}
