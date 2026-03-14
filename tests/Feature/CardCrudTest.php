<?php

use App\Models\Card;
use App\Models\Customer;
use App\Models\User;

test('card can be created for customer', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();

    $response = $this->actingAs($user)->post(
        route('customers.cards.store', $customer),
        [
            'name' => 'Charizard',
            'status' => 'pending',
        ]
    );

    $response->assertRedirect(route('customers.show', $customer));
    $this->assertDatabaseHas('cards', [
        'customer_id' => $customer->id,
        'name' => 'Charizard',
        'status' => 'pending',
    ]);
});

test('card store forbidden for other users customer', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    $response = $this->actingAs($user)->post(
        route('customers.cards.store', $customer),
        ['name' => 'Card', 'status' => 'pending']
    );

    $response->assertForbidden();
});

test('card estimated_fee is computed from restoration_hours and business hourly rate', function () {
    $user = User::factory()->create();
    $user->businessSettings()->create(['hourly_rate' => 100, 'currency' => 'USD']);
    $customer = Customer::factory()->for($user)->create();

    $this->actingAs($user)->post(
        route('customers.cards.store', $customer),
        [
            'name' => 'Test Card',
            'status' => 'pending',
            'restoration_hours' => 2.5,
        ]
    );

    $card = Card::where('customer_id', $customer->id)->first();
    expect((float) $card->estimated_fee)->toBe(250.0); // 2.5 * 100
});

test('card estimated_fee uses user business settings hourly rate when set', function () {
    $user = User::factory()->create();
    $user->businessSettings()->create(['hourly_rate' => 80, 'currency' => 'USD']);
    $customer = Customer::factory()->for($user)->create();

    $this->actingAs($user)->post(
        route('customers.cards.store', $customer),
        [
            'name' => 'Test Card',
            'status' => 'pending',
            'restoration_hours' => 2.5,
        ]
    );

    $card = Card::where('customer_id', $customer->id)->first();
    expect((float) $card->estimated_fee)->toBe(200.0); // 2.5 * 80
});

test('card can be updated', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $card = Card::factory()->for($customer)->create(['name' => 'Old Name']);

    $response = $this->actingAs($user)->patch(
        route('customers.cards.update', [$customer, $card]),
        ['name' => 'New Name', 'status' => 'in_progress']
    );

    $response->assertRedirect(route('customers.show', $customer));
    $card->refresh();
    expect($card->name)->toBe('New Name');
    expect($card->status->value)->toBe('in_progress');
});

test('card update forbidden for other user', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    $card = Card::factory()->for($customer)->create();

    $response = $this->actingAs($user)->patch(
        route('customers.cards.update', [$customer, $card]),
        ['name' => 'Hacked', 'status' => 'pending']
    );

    $response->assertForbidden();
});

test('card can be deleted', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $card = Card::factory()->for($customer)->create();

    $response = $this->actingAs($user)->delete(
        route('customers.cards.destroy', [$customer, $card])
    );

    $response->assertRedirect(route('customers.show', $customer));
    $this->assertDatabaseMissing('cards', ['id' => $card->id]);
});
