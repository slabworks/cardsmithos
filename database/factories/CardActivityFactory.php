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
            'title' => fake()->randomElement([
                'Card received and inspected',
                'Surface cleaning completed',
                'Pressing started — 140°F cycle',
                'Before photos taken',
                'Grading prep completed',
                'Ready for return shipping',
                'Card shipped back to customer',
                'Humidity chamber treatment started',
                'Corner repair in progress',
                'After photos taken and sent',
                'PSA submission paperwork prepared',
                'Card re-sleeved and toploaded',
            ]),
            'description' => fake()->optional(0.7)->randomElement([
                'Inspected under magnification. Found light surface scratches on holo and minor whitening on back corners. Estimated grade improvement: LP to NM.',
                '24-hour press cycle complete. Card is now flat with no visible warping. Edges look clean.',
                'Took high-res scans of front and back before starting any work. Uploaded to customer portal.',
                'Surface cleaned with soft eraser and microfiber. Holo looks significantly better — no more fingerprint smudges.',
                'Card placed in humidity chamber at 62% RH for rehydration before pressing. Will check in 12 hours.',
                'Completed all restoration work. Card is sleeved, toploaded, and ready for grading submission or return.',
                'Shipped via USPS Priority Mail with tracking. Customer notified by email.',
                'Minor whitening on top-left corner addressed. Back surface cleaned. Overall condition improved from MP to LP.',
                'Card is in excellent shape — no restoration needed. Documented current condition and prepped for CGC submission.',
                'Customer approved the estimate. Starting work tomorrow — expect 3-day turnaround on this batch.',
            ]),
            'occurred_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
