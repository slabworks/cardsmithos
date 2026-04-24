<?php

namespace Database\Seeders;

use App\Models\BusinessSettings;
use App\Models\Card;
use App\Models\CardActivity;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Ash Ketchum',
            'email' => 'ash@pokemon.co',
        ]);

        $this->call(BusinessStatisticSeeder::class);

        // Set up Ash's business with realistic rates
        $fixedRate = 35.00;
        BusinessSettings::factory()->for($user)->create([
            'company_name' => 'Pallet Town Card Restoration',
            'store_slug' => 'pallet-town-card-restoration',
            'hourly_rate' => 50.00,
            'default_fixed_rate' => $fixedRate,
            'currency' => 'USD',
            'tax_rate' => 8.25,
            'instagram_handle' => 'palletcards',
            'tiktok_handle' => 'palletcards',
            'country' => 'US',
        ]);

        $customers = Customer::factory()
            ->count(5)
            ->for($user)
            ->create()
            ->each(function (Customer $customer) use ($fixedRate): void {
                $cards = Card::factory()
                    ->count(rand(2, 6))
                    ->for($customer)
                    ->create()
                    ->each(function (Card $card): void {
                        CardActivity::factory()
                            ->count(rand(0, 5))
                            ->for($card)
                            ->create();
                    });

                // Create 1-3 payments spread across the last 12 months
                // Amounts reflect the fixed rate x number of cards in a batch
                // Shipments are tied to the same months so revenue never goes negative
                $paymentCount = rand(1, 3);
                for ($i = 0; $i < $paymentCount; $i++) {
                    $cardsInBatch = rand(1, min(4, $cards->count()));
                    $amount = $fixedRate * $cardsInBatch;

                    $monthsAgo = rand(0, 11);
                    $start = CarbonImmutable::now()->subMonths($monthsAgo + 1);
                    $end = CarbonImmutable::now()->subMonths($monthsAgo);
                    $date = fake()->dateTimeBetween($start, $end)->format('Y-m-d');

                    Payment::factory()
                        ->for($customer)
                        ->create([
                            'amount' => $amount,
                            'paid_at' => $date,
                        ]);

                    // ~50% chance of a shipment in the same month, always less than the payment
                    if (fake()->boolean(50)) {
                        Shipment::factory()
                            ->for($customer)
                            ->create([
                                'amount' => fake()->randomFloat(2, 5, min(15, $amount * 0.3)),
                                'shipped_at' => $date,
                            ]);
                    }
                }
            });

        // Seed expenses
        Expense::factory()
            ->count(8)
            ->for($user)
            ->create();

        // Seed directory storefronts
        BusinessSettings::factory()->count(20)->create();

        // Add a couple with "Other" country
        BusinessSettings::factory()->otherCountry('Singapore')->create();
        BusinessSettings::factory()->otherCountry('Philippines')->create();
    }
}
