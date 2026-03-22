<?php

namespace Database\Factories;

use App\Enums\CardCondition;
use App\Enums\CardStatus;
use App\Models\Card;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Card>
 */
class CardFactory extends Factory
{
    private const CARD_NAMES = [
        'Charizard Base Set Holo 4/102',
        'Dark Magician LOB-005 1st Edition',
        'Black Lotus Alpha',
        '1986 Fleer Michael Jordan #57',
        'Pikachu Illustrator Promo',
        'Blue-Eyes White Dragon SDK-001',
        'Mew Gold Star EX Dragon Frontiers',
        'Ken Griffey Jr. 1989 Upper Deck #1',
        'Blastoise Base Set Holo 2/102',
        'Lugia Neo Genesis 1st Edition',
        'Shining Mewtwo Neo Destiny',
        'Espeon Gold Star Pop Series 5',
        'Jace, the Mind Sculptor Worldwake',
        'Exodia the Forbidden One LOB-124',
        'Venusaur Base Set Holo 15/102',
        'Mickey Mantle 1952 Topps #311',
        'Umbreon VMAX Alt Art Evolving Skies',
        'Lillie Full Art Ultra Prism',
        'Red-Eyes Black Dragon LOB-070',
        'Rayquaza Gold Star EX Deoxys',
        'Kobe Bryant 1996 Topps Chrome #138',
        'Mewtwo Base Set Holo 10/102',
        'Dark Charizard Team Rocket 1st Ed',
        'Alakazam Base Set Holo 1/102',
        'Moonbreon VMAX Alt Art Evolving Skies',
        'Mike Trout 2011 Topps Update #US175',
        'Crystal Charizard Skyridge',
        'Pot of Greed SDP-035',
        'Gengar Masaki Promo',
        'Tom Brady 2000 Playoff Contenders #144',
    ];

    private const WORK_DONE = [
        'Surface cleaned with archival eraser. Removed light whitening on back edges. Pressed at 140°F for 24 hours.',
        'Rehydrated card to remove warp. Corner micro-repair on top-left. Light cleaning on holo surface.',
        'Full press cycle completed — card flattened successfully. Minor surface scratches remain but centering improved.',
        'Cleaned holo surface with microfiber. Removed fingerprint residue. Card is now grading-ready.',
        'Deep cleaning on front and back. Removed sticky residue from top edge. Pressed overnight at low heat.',
        'Whitening reduction on all four corners. Surface buffed lightly. Estimated grade improvement: LP to NM.',
        'Card was heavily warped. Two-stage press: humidity chamber then 48-hour flat press. Result is excellent.',
        'Grading prep only — sleeved, toploaded, and documented. No restoration needed, card is already clean.',
        'Removed light surface scratches on holo with careful buffing. Back edges cleaned. Ready for PSA submission.',
        'Vintage card — handled with extra care. Light cleaning only, no pressing due to age and paper stock.',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'name' => fake()->randomElement(self::CARD_NAMES),
            'work_done' => fake()->optional(0.5)->randomElement(self::WORK_DONE),
            'status' => fake()->randomElement(CardStatus::cases()),
            'condition_before' => fake()->optional(0.8)->randomElement(CardCondition::cases()),
            'condition_after' => null,
            'restoration_hours' => fake()->optional(0.6)->randomFloat(2, 0.5, 20),
            'estimated_fee' => null,
            'photos' => null,
        ];
    }
}
