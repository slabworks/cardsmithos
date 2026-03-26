<?php

namespace Database\Factories;

use App\Models\BusinessSettings;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessSettings>
 */
class BusinessSettingsFactory extends Factory
{
    private const SHOP_NAMES = [
        'SlabWorks',
        'Card Revival Co.',
        'Pristine Cards',
        'The Card Doctor',
        'Mint Condition Studio',
        'Grade Up Cards',
        'Card Care Pros',
        'Restoration Station',
        'Slab & Seal',
        'CardMedic',
        'TopDeck Repairs',
        'Fresh Slab Co.',
        'The Grading Table',
        'CrispCard Studio',
        'Gem Mint Repairs',
        'Holo Fix',
        'Card Surgeon',
        'Snap Grading Co.',
        'Sleeve & Seal',
        'PokéRestore',
        'TCG Clinic',
        'Card Alchemy',
        'Binder Fresh',
        'Ultra Rare Repairs',
        'Centering Lab',
    ];

    private const COUNTRIES = [
        'US', 'JP', 'GB', 'CA', 'AU', 'DE', 'FR', 'IT', 'ES', 'NL',
        'BR', 'MX', 'KR', 'TW', 'NZ', 'SE', 'NO', 'DK', 'FI', 'BE',
    ];

    private const BIOS = [
        'Professional trading card restoration and grading prep. We specialize in vintage Pokemon, Yu-Gi-Oh!, and Magic: The Gathering cards. Every card is treated with museum-grade care.',
        "Bringing your cards back to life, one slab at a time. Whether it's a childhood favorite or a new addition to your collection, we can bring trading cards back to life with safe, ethical restoration.",
        'Expert card cleaning, pressing, and restoration services. We use archival-safe methods to improve card condition without altering authenticity. PSA & CGC grading prep available.',
        'Full-service card restoration shop. From surface cleaning to corner repair, we handle it all. Fast turnaround and competitive pricing for bulk orders.',
        'Dedicated to preserving the hobby. We offer professional card pressing, cleaning, and restoration with transparent before/after documentation for every card.',
        'Premium card care services for serious collectors. Specializing in high-value vintage cards. Insured shipping and white-glove handling on every order.',
        "Your local card restoration experts. We've restored thousands of cards across all major TCGs. Satisfaction guaranteed or your money back.",
        "Card restoration done right. We focus on Pokemon TCG cards exclusively, giving us unmatched expertise in the hobby's most popular cards.",
        'Fast, affordable card restoration for collectors of all levels. We offer pressing, cleaning, and grading prep services with a 5-day turnaround.',
        'Boutique card restoration studio. We take on a limited number of cards each month to ensure every piece gets the attention it deserves.',
    ];

    public function definition(): array
    {
        $shopName = fake()->unique()->randomElement(self::SHOP_NAMES);
        $slug = str($shopName)->slug()->toString();
        $country = fake()->randomElement(self::COUNTRIES);

        return [
            'user_id' => User::factory(),
            'company_name' => $shopName,
            'store_slug' => $slug,
            'hourly_rate' => fake()->randomElement([null, fake()->randomFloat(2, 15, 150)]),
            'default_fixed_rate' => fake()->randomElement([null, fake()->randomFloat(2, 5, 75)]),
            'currency' => 'USD',
            'tax_rate' => fake()->randomElement([null, 0, 5, 7.5, 8.25, 8.5, 10, 13]),
            'bio' => fake()->randomElement(self::BIOS),
            'instagram_handle' => fake()->optional(0.6)->lexify('????cards'),
            'tiktok_handle' => fake()->optional(0.4)->lexify('????cards'),
            'country' => $country,
            'location_name' => null,
            'hide_pricing' => fake()->boolean(20),
        ];
    }

    public function withoutSlug(): static
    {
        return $this->state(fn () => [
            'store_slug' => null,
        ]);
    }

    public function otherCountry(string $name = 'Singapore'): static
    {
        return $this->state(fn () => [
            'country' => 'OT',
            'location_name' => $name,
        ]);
    }
}
