<?php

use App\Models\Customer;
use App\Models\Payment;
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
        ->has('totalCustomers')
        ->has('newestCustomer')
        ->has('revenueByMonth')
        ->where('totalPayments', 0)
        ->where('totalCustomers', 0)
        ->where('newestCustomer', null)
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
        ->where('totalCustomers', 2)
        ->has('newestCustomer')
        ->where('newestCustomer.name', 'Newest Customer')
        ->where('newestCustomer.id', $customer2->id)
        ->has('revenueByMonth')
    );
});
