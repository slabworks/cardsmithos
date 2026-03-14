<?php

use App\Models\Customer;
use App\Models\Payment;
use App\Models\User;

test('payment can be created for customer', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();

    $response = $this->actingAs($user)->post(
        route('customers.payments.store', $customer),
        [
            'amount' => 150.00,
            'paid_at' => '2025-01-15',
            'method' => 'cash',
        ]
    );

    $response->assertRedirect(route('customers.show', $customer));
    $this->assertDatabaseHas('payments', [
        'customer_id' => $customer->id,
        'amount' => 150.00,
        'method' => 'cash',
    ]);
});

test('payment store forbidden for other users customer', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    $response = $this->actingAs($user)->post(
        route('customers.payments.store', $customer),
        [
            'amount' => 100,
            'paid_at' => '2025-01-01',
            'method' => 'card',
        ]
    );

    $response->assertForbidden();
});

test('payment can be updated', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $payment = Payment::factory()->for($customer)->create([
        'amount' => 50,
        'method' => 'cash',
    ]);

    $response = $this->actingAs($user)->patch(
        route('customers.payments.update', [$customer, $payment]),
        [
            'amount' => 75.50,
            'paid_at' => '2025-02-01',
            'method' => 'bank_transfer',
            'reference' => 'INV-001',
        ]
    );

    $response->assertRedirect(route('customers.show', $customer));
    $payment->refresh();
    expect((float) $payment->amount)->toBe(75.50);
    expect($payment->method->value)->toBe('bank_transfer');
    expect($payment->reference)->toBe('INV-001');
});

test('payment update forbidden for other user', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    $payment = Payment::factory()->for($customer)->create();

    $response = $this->actingAs($user)->patch(
        route('customers.payments.update', [$customer, $payment]),
        [
            'amount' => 999,
            'paid_at' => '2025-01-01',
            'method' => 'cash',
        ]
    );

    $response->assertForbidden();
});

test('payment can be deleted', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $payment = Payment::factory()->for($customer)->create();

    $response = $this->actingAs($user)->delete(
        route('customers.payments.destroy', [$customer, $payment])
    );

    $response->assertRedirect(route('customers.show', $customer));
    $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
});
