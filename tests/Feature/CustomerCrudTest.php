<?php

use App\Models\Customer;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('customer index lists only own customers', function () {
    $user = User::factory()->create();
    Customer::factory()->for($user)->count(2)->create();
    Customer::factory()->create();

    $response = $this->actingAs($user)->get(route('customers.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('customers/index')
        ->has('customers', 2));
});

test('customer can be created', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('customers.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);

    $response->assertRedirect(route('customers.index'));
    $this->assertDatabaseHas('customers', [
        'user_id' => $user->id,
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);
});

test('creating a customer creates a service waiver', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('customers.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);

    $customer = Customer::where('email', 'jane@example.com')->first();
    expect($customer)->not->toBeNull();
    expect($customer->serviceWaiver)->not->toBeNull();
    expect($customer->serviceWaiver->expires_at->isFuture())->toBeTrue();
});

test('customer show displays customer', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create(['name' => 'Acme']);

    $response = $this->actingAs($user)->get(route('customers.show', $customer));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('customers/show')
        ->where('customer.name', 'Acme'));
});

test('customer show includes waiver URL when waiver not signed', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $customer->serviceWaiver()->create([
        'expires_at' => now()->addDays(30),
    ]);

    $response = $this->actingAs($user)->get(route('customers.show', $customer));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('customers/show')
        ->where('waiverUrl', fn ($url) => str_contains($url, '/waiver/'.$customer->id) && str_contains($url, 'signature=')));
});

test('customer show forbidden for other user', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    $response = $this->actingAs($user)->get(route('customers.show', $customer));

    $response->assertForbidden();
});

test('customer can be updated', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();

    $response = $this->actingAs($user)->patch(
        route('customers.update', $customer),
        ['name' => 'Updated Name', 'email' => 'updated@example.com']
    );

    $response->assertRedirect(route('customers.show', $customer));
    $customer->refresh();
    expect($customer->name)->toBe('Updated Name');
    expect($customer->email)->toBe('updated@example.com');
});

test('customer update forbidden for other user', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    $response = $this->actingAs($user)->patch(
        route('customers.update', $customer),
        ['name' => 'Hacked']
    );

    $response->assertForbidden();
});

test('customer can be deleted', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();

    $response = $this->actingAs($user)->delete(route('customers.destroy', $customer));

    $response->assertRedirect(route('customers.index'));
    $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
});
