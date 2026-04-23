<?php

use App\Models\Customer;
use App\Models\Shipment;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('shipment index lists only owned shipments', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    Shipment::factory()->for($customer)->count(2)->create();
    Shipment::factory()->create();

    $response = $this->actingAs($user)->get(route('shipments.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('shipments/index')
        ->has('shipments', 2));
});

test('shipment create page renders', function () {
    $user = User::factory()->create();
    Customer::factory()->for($user)->create();

    $response = $this->actingAs($user)->get(route('shipments.create'));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('shipments/create')
        ->has('customers'));
});

test('shipment can be created', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();

    $response = $this->actingAs($user)->post(route('shipments.store'), [
        'customer_id' => $customer->id,
        'amount' => 18.75,
        'shipped_at' => '2026-03-19',
        'tracking_number' => '1Z999AA10123456784',
        'reference' => 'RETURN-LABEL',
    ]);

    $response->assertRedirect(route('shipments.index'));
    $this->assertDatabaseHas('shipments', [
        'customer_id' => $customer->id,
        'amount' => 18.75,
        'shipped_at' => '2026-03-19 00:00:00',
        'tracking_number' => '1Z999AA10123456784',
        'reference' => 'RETURN-LABEL',
    ]);
});

test('shipment creation validates customer ownership', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    $response = $this->actingAs($user)->post(route('shipments.store'), [
        'customer_id' => $customer->id,
        'amount' => 18.75,
        'shipped_at' => '2026-03-19',
    ]);

    $response->assertForbidden();
});

test('shipment can be updated', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $shipment = Shipment::factory()->for($customer)->create();

    $response = $this->actingAs($user)->put(route('shipments.update', $shipment), [
        'customer_id' => $customer->id,
        'amount' => 25.00,
        'shipped_at' => '2026-03-20',
        'tracking_number' => 'UPDATED-TRACKING',
        'reference' => 'UPDATED-REF',
    ]);

    $response->assertRedirect(route('shipments.index'));
    $shipment->refresh();
    expect((float) $shipment->amount)->toBe(25.00);
    expect($shipment->tracking_number)->toBe('UPDATED-TRACKING');
});

test('shipment can be deleted', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $shipment = Shipment::factory()->for($customer)->create();

    $response = $this->actingAs($user)->delete(route('shipments.destroy', $shipment));

    $response->assertRedirect(route('shipments.index'));
    $this->assertDatabaseMissing('shipments', ['id' => $shipment->id]);
});
