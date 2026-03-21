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
            'inquiry_name' => fake()->name(),
            'contact_detail' => fake()->randomElement([
                'pokefan_2024', 'vintagecardsonly', '@slabcollector', 'tcg.mike',
                'graded.gems', 'collector.jake@gmail.com', 'holoheaven',
                '@mintcondition.co', 'basesetbrad', 'cardvault_chris',
                'psa10hunter', '@yugioh.restores', 'slablife_88',
                'rarecards.emma@yahoo.com', 'topdecktrading',
            ]),
            'communication_method' => fake()->randomElement(CommunicationMethod::cases()),
            'inquired_at' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'converted' => fake()->boolean(25),
            'notes' => fake()->optional(0.4)->randomElement([
                'Wants to get 5 vintage Pokemon holos restored before PSA submission. Asking about turnaround time.',
                'Interested in bulk pricing for 30+ cards. Mostly modern Yu-Gi-Oh! ultras.',
                'Asked about our pressing process — wants to know if it affects authenticity.',
                'Has a 1st Edition Base Set collection. Looking for grading prep on the big 3 (Zard, Blast, Venus).',
                'Referred by another customer. Wants to ship 10 sports cards for cleaning.',
                'Messaged on Instagram asking about pricing. Sent them the rate card.',
                'Wants a quote for restoring a heavily played Dark Magician. Sent photos.',
                'Local collector — asked if they can drop cards off in person instead of shipping.',
                'Interested in our CGC submission service. Has about 15 modern Pokemon cards.',
                'Asked about turnaround time for a rush order. Needs cards back before a tournament.',
            ]),
        ];
    }
}
