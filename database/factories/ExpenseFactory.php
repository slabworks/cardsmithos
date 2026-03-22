<?php

namespace Database\Factories;

use App\Enums\ExpenseCategory;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
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
            'description' => fake()->randomElement([
                'KMC Perfect Fit sleeves (1000 ct)',
                'Boveda 62% humidity packs',
                'Ultra Pro toploaders (100 ct)',
                'Penny sleeves bulk box',
                'Archival-safe erasers (12 pk)',
                'USPS Priority Mail labels',
                'Bubble mailers (50 ct)',
                'Packing tape rolls',
                'Shipping scale batteries',
                'Fragile stickers for packages',
                'LED magnification lamp',
                'Card pressing iron replacement',
                'Humidity-controlled storage cabinet',
                'Microfiber cloths (bulk)',
                'Digital caliper for centering',
                'CardSmithOS monthly plan',
                'Adobe Lightroom subscription',
                'Google Workspace renewal',
                'Instagram sponsored post — grading giveaway',
                'Card show booth fee — Dallas Expo',
                'Business cards reprint (500 ct)',
                'Banner for card show booth',
            ]),
            'amount' => fake()->randomFloat(2, 5, 500),
            'category' => fake()->randomElement(ExpenseCategory::cases()),
            'occurred_at' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'notes' => fake()->optional(0.3)->randomElement([
                'Restocked — running low after last batch of orders.',
                'Bought in bulk for better pricing. Should last 3 months.',
                'Needed for PSA bulk submission packaging.',
                'Monthly recurring expense.',
                'One-time purchase, upgrading from old equipment.',
                'Promotional campaign for new grading prep service.',
            ]),
        ];
    }
}
