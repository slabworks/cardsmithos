<?php

namespace Database\Seeders;

use App\Models\BusinessSettings;
use App\Models\Card;
use App\Models\CardActivity;
use App\Models\Customer;
use App\Models\Inquiry;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $customers = Customer::factory()
            ->count(5)
            ->for($user)
            ->create()
            ->each(function (Customer $customer): void {
                Card::factory()
                    ->count(rand(2, 6))
                    ->for($customer)
                    ->create()
                    ->each(function (Card $card): void {
                        CardActivity::factory()
                            ->count(rand(0, 5))
                            ->for($card)
                            ->create();
                    });
                Payment::factory()
                    ->count(rand(0, 4))
                    ->for($customer)
                    ->create();
            });

        // Create inquiries — some linked to customers, some not, some converted
        Inquiry::factory()
            ->count(5)
            ->for($user)
            ->create();

        $customers->take(3)->each(function (Customer $customer) use ($user): void {
            Inquiry::factory()
                ->for($user)
                ->for($customer)
                ->create(['converted' => true]);
        });

        // Seed directory storefronts
        BusinessSettings::factory()->count(20)->create();

        // Add a couple with "Other" country
        BusinessSettings::factory()->otherCountry('Singapore')->create();
        BusinessSettings::factory()->otherCountry('Philippines')->create();
    }
}
