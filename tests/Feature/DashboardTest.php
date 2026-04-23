<?php

use App\Enums\CardStatus;
use App\Models\Card;
use App\Models\Customer;
use App\Models\Expense;
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
        ->has('totalCustomers')
        ->has('convertedCustomers')
        ->has('revenueByMonth')
        ->where('totalPayments', 0)
        ->where('totalShipmentFees', 0)
        ->where('totalExpenses', 0)
        ->where('totalCustomers', 0)
        ->where('convertedCustomers', 0)
        ->has('revenueByMonth')
        ->has('cardsByStatus', fn ($prop) => $prop
            ->has('backlog')
            ->has('pending')
            ->has('in_progress')
        )
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
        ->where('totalPayments', 225)
        ->where('totalCustomers', 2)
        ->where('convertedCustomers', 0)
        ->has('revenueByMonth')
    );
});

test('dashboard shows customer conversion stats', function () {
    $user = User::factory()->create();

    Customer::factory()->for($user)->count(3)->create([
        'converted' => false,
    ]);
    Customer::factory()->for($user)->create([
        'converted' => true,
    ]);

    $this->actingAs($user);
    $response = $this->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('dashboard')
        ->where('totalCustomers', 4)
        ->where('convertedCustomers', 1)
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
    Expense::factory()->for($user)->create([
        'amount' => 20.00,
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
        ->where('totalPayments', 105)
        ->where('totalShipmentFees', 25)
        ->where('totalExpenses', 20)
        ->where('revenueByMonth.10.total', 40)
        ->where('revenueByMonth.11.total', 85)
    );
});

test('dashboard groups kanban cards by status', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();

    $backlogCard = Card::factory()->for($customer)->create([
        'name' => 'Backlog Card',
        'status' => CardStatus::Backlog,
    ]);
    $pendingCard = Card::factory()->for($customer)->create([
        'name' => 'Pending Card',
        'status' => CardStatus::Pending,
    ]);
    $inProgressCard = Card::factory()->for($customer)->create([
        'name' => 'In Progress Card',
        'status' => CardStatus::InProgress,
    ]);

    $this->actingAs($user);
    $response = $this->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('dashboard')
        ->has('cardsByStatus.backlog', 1)
        ->has('cardsByStatus.pending', 1)
        ->has('cardsByStatus.in_progress', 1)
        ->where('cardsByStatus.backlog.0.name', 'Backlog Card')
        ->where('cardsByStatus.pending.0.name', 'Pending Card')
        ->where('cardsByStatus.in_progress.0.name', 'In Progress Card')
        ->where('cardsByStatus.backlog.0.customer.name', $customer->name)
    );
});

test('dashboard excludes repaired cards from kanban board', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();

    Card::factory()->for($customer)->create([
        'status' => CardStatus::Repaired,
    ]);
    Card::factory()->for($customer)->create([
        'status' => CardStatus::Backlog,
    ]);

    $this->actingAs($user);
    $response = $this->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('dashboard')
        ->has('cardsByStatus.backlog', 1)
        ->has('cardsByStatus.pending', 0)
        ->has('cardsByStatus.in_progress', 0)
    );
});

test('dashboard excludes other users cards from kanban board', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $customer = Customer::factory()->for($user)->create();
    $otherCustomer = Customer::factory()->for($otherUser)->create();

    Card::factory()->for($customer)->create(['status' => CardStatus::Backlog]);
    Card::factory()->for($otherCustomer)->create(['status' => CardStatus::Backlog]);

    $this->actingAs($user);
    $response = $this->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('dashboard')
        ->has('cardsByStatus.backlog', 1)
    );
});
