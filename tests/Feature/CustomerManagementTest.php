<?php

use App\Enums\CustomerPlatform;
use App\Models\Customer;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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

test('customer index can be searched', function () {
    $user = User::factory()->create();
    Customer::factory()->for($user)->create(['name' => 'Jane Doe']);
    Customer::factory()->for($user)->create(['name' => 'Acme Cards']);

    $response = $this->actingAs($user)->get(route('customers.index', ['search' => 'Jane']));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('customers/index')
        ->has('customers', 1)
        ->where('customers.0.name', 'Jane Doe')
        ->where('filters.search', 'Jane'));
});

test('customer create page can be viewed', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('customers.create'));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('customers/create')
        ->has('platformOptions'));
});

test('customer can be created', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('customers.store'), [
        'name' => 'Jane Doe',
        'contact_detail' => '@janedoe',
        'platform' => CustomerPlatform::TikTok->value,
        'phone' => '555-1234',
        'address' => '123 Main St',
    ]);

    $customer = Customer::query()->where('user_id', $user->id)->first();
    expect($customer)->not->toBeNull();
    $response->assertRedirect(route('customers.edit', $customer));
    $this->assertDatabaseHas('customers', [
        'user_id' => $user->id,
        'name' => 'Jane Doe',
        'contact_detail' => '@janedoe',
        'platform' => CustomerPlatform::TikTok->value,
    ]);

    expect($customer->address)->toBe('123 Main St')
        ->and(DB::table('customers')->where('id', $customer->id)->value('address'))->not->toBe('123 Main St');
});

test('customer edit page includes submissions', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create(['name' => 'Jane Doe']);
    Submission::factory()->for($user)->for($customer)->create();

    $response = $this->actingAs($user)->get(route('customers.edit', $customer));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('customers/edit')
        ->where('customer.name', 'Jane Doe')
        ->has('platformOptions')
        ->has('customer.submissions', 1));
});

test('customer can be updated', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();

    $response = $this->actingAs($user)->patch(route('customers.update', $customer), [
        'name' => 'Updated Customer',
        'contact_detail' => 'updated@example.com',
        'platform' => CustomerPlatform::Email->value,
        'phone' => '555-0000',
        'address' => '456 Oak Ave',
    ]);

    $response->assertRedirect(route('customers.edit', $customer));
    $this->assertDatabaseHas('customers', [
        'id' => $customer->id,
        'name' => 'Updated Customer',
        'contact_detail' => 'updated@example.com',
        'platform' => CustomerPlatform::Email->value,
    ]);

    $customer->refresh();

    expect($customer->address)->toBe('456 Oak Ave')
        ->and(DB::table('customers')->where('id', $customer->id)->value('address'))->not->toBe('456 Oak Ave');
});

test('customer edit forbidden for other user', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    $response = $this->actingAs($user)->get(route('customers.edit', $customer));

    $response->assertForbidden();
});

test('customer update forbidden for other user', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    $response = $this->actingAs($user)->patch(route('customers.update', $customer), [
        'name' => 'Hacked',
    ]);

    $response->assertForbidden();
});
