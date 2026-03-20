<?php

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('dashboard')
        ->has('totalPayments')
        ->has('totalShipmentFees')
        ->has('totalExpenses')
        ->has('revenueByMonth')
        ->where('totalPayments', 0)
        ->where('totalShipmentFees', 0)
        ->where('totalExpenses', 0)
        ->has('revenueByMonth')
    );
});

test('dashboard shows stats and newest customer when user has customers and payments', function () {
    $user = User::factory()->create();
    $customer1 = Customer::factory()->for($user)->create([
        'name' => 'First Customer',
        'created_at' => now()->subDay(),
    ]);
    $customer2 = Customer::factory()->for($user)->create([
        'name' => 'Newest Customer',
        'created_at' => now(),
    ]);
    Payment::factory()->for($customer1)->create(['amount' => 100.50]);
    Payment::factory()->for($customer1)->create(['amount' => 49.50]);
    Payment::factory()->for($customer2)->create(['amount' => 75.00]);

    $this->actingAs($user);
    $response = $this->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('dashboard')
        ->where('totalPayments', 225)
        ->where('totalShipmentFees', 0)
        ->where('totalExpenses', 0)
        ->has('revenueByMonth')
    );
});

test('dashboard subtracts shipment fees from all payment-derived metrics', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();

    Payment::factory()->for($customer)->create([
        'amount' => 100.00,
        'paid_at' => now()->format('Y-m-d'),
    ]);
    Payment::factory()->for($customer)->create([
        'amount' => 50.00,
        'paid_at' => now()->subMonth()->format('Y-m-d'),
    ]);

    Shipment::factory()->for($customer)->create([
        'amount' => 15.00,
        'shipped_at' => now()->format('Y-m-d'),
    ]);
    Shipment::factory()->for($customer)->create([
        'amount' => 10.00,
        'shipped_at' => now()->subMonth()->format('Y-m-d'),
    ]);

    $otherUser = User::factory()->create();
    $otherCustomer = Customer::factory()->for($otherUser)->create();
    Payment::factory()->for($otherCustomer)->create(['amount' => 999.00]);
    Shipment::factory()->for($otherCustomer)->create(['amount' => 500.00]);

    $this->actingAs($user);
    $response = $this->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('dashboard')
        ->where('totalPayments', 125)
        ->where('totalShipmentFees', 25)
        ->where('revenueByMonth.10.total', 40)
        ->where('revenueByMonth.11.total', 85)
    );
});
