<?php

namespace Database\Factories;

use App\Enums\SubmissionReferralSource;
use App\Enums\SubmissionStatus;
use App\Models\Customer;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Submission>
 */
class SubmissionFactory extends Factory
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
            'customer_id' => Customer::factory(),
            'status' => fake()->randomElement(SubmissionStatus::cases()),
            'notes' => fake()->optional(0.3)->randomElement([
                'Repeat customer, prefers bubble mailer. Has a large vintage Pokemon collection.',
                'Bulk order - 20+ cards for grading prep. Wants PSA submission.',
                'First-time customer. Found us through Instagram. Sent in 3 base set holos.',
                'Collector focused on Yu-Gi-Oh! 1st editions. Very particular about handling.',
                'Sports card collector - mostly vintage baseball. Ships via Priority Mail only.',
                'Wants cards back ASAP for eBay listings. Paid for rush turnaround.',
                'Long-time customer, always sends Pokemon gold stars. Trusts our process.',
                'Inquired about bulk discount for 50+ cards. Mostly modern ultras.',
                'Prefers detailed before/after photos for each card. Posts results on Reddit.',
                'Local collector - drops off and picks up in person. No shipping needed.',
            ]),
            'referral_source' => fake()->optional(0.4)->randomElement(
                array_map(fn (SubmissionReferralSource $case) => $case->value, SubmissionReferralSource::cases())
            ),
        ];
    }
}
