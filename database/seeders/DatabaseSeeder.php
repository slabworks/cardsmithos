<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Models\Customer;
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

        Customer::factory()
            ->count(5)
            ->for($user)
            ->create()
            ->each(function (Customer $customer): void {
                Card::factory()
                    ->count(rand(2, 6))
                    ->for($customer)
                    ->create();
                Payment::factory()
                    ->count(rand(0, 4))
                    ->for($customer)
                    ->create();
            });
    }
}
