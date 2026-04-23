<?php

use App\Models\Customer;
use App\Models\Payment;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('payment index lists only owned payments', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    Payment::factory()->for($customer)->count(2)->create();
    Payment::factory()->create();

    $response = $this->actingAs($user)->get(route('payments.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('payments/index')
        ->has('payments', 2));
});

test('payment create page renders', function () {
    $user = User::factory()->create();
    Customer::factory()->for($user)->create();

    $response = $this->actingAs($user)->get(route('payments.create'));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('payments/create')
        ->has('customers'));
});

test('payment can be created', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();

    $response = $this->actingAs($user)->post(route('payments.store'), [
        'customer_id' => $customer->id,
        'amount' => 150.00,
        'paid_at' => '2026-03-17',
    ]);

    $response->assertRedirect(route('payments.index'));
    $this->assertDatabaseHas('payments', [
        'customer_id' => $customer->id,
        'amount' => 150.00,
        'paid_at' => '2026-03-17 00:00:00',
    ]);
});

test('payment creation validates customer ownership', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    $response = $this->actingAs($user)->post(route('payments.store'), [
        'customer_id' => $customer->id,
        'amount' => 150.00,
        'paid_at' => '2026-03-17',
    ]);

    $response->assertForbidden();
});

test('payment can be updated', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $payment = Payment::factory()->for($customer)->create([
        'amount' => 50,
    ]);

    $response = $this->actingAs($user)->put(route('payments.update', $payment), [
        'customer_id' => $customer->id,
        'amount' => 75.50,
        'paid_at' => '2026-03-18',
    ]);

    $response->assertRedirect(route('payments.index'));
    $payment->refresh();
    expect((float) $payment->amount)->toBe(75.50);
});

test('payment can be deleted', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $payment = Payment::factory()->for($customer)->create();

    $response = $this->actingAs($user)->delete(route('payments.destroy', $payment));

    $response->assertRedirect(route('payments.index'));
    $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
});
