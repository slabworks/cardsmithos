<?php

use App\Models\Customer;
use App\Models\Shipment;
use App\Models\User;

test('shipment can be created for customer', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();

    $response = $this->actingAs($user)->post(
        route('customers.shipments.store', $customer),
        [
            'amount' => 18.75,
            'shipped_at' => '2026-03-19',
            'reference' => 'RETURN-LABEL',
            'tracking_number' => '1Z999AA10123456784',
        ]
    );

    $response->assertRedirect(route('customers.show', $customer));
    $this->assertDatabaseHas('shipments', [
        'customer_id' => $customer->id,
        'amount' => 18.75,
        'reference' => 'RETURN-LABEL',
        'tracking_number' => '1Z999AA10123456784',
    ]);
});

test('shipment store forbidden for other users customer', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    $response = $this->actingAs($user)->post(
        route('customers.shipments.store', $customer),
        [
            'amount' => 14.00,
            'shipped_at' => '2026-03-19',
        ]
    );

    $response->assertForbidden();
    expect(Shipment::query()->count())->toBe(0);
});
