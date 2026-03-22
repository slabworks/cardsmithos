<?php

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\URL;

test('signed waiver URL shows waiver form when not signed', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create(['name' => 'Jane', 'email' => 'jane@example.com']);
    $waiver = $customer->serviceWaiver()->create([
        'expires_at' => now()->addDays(30),
    ]);

    $url = URL::temporarySignedRoute(
        'waiver.show',
        $waiver->expires_at,
        ['customer' => $customer],
        absolute: false
    );

    $response = $this->get($url);

    $response->assertSuccessful();
    $response->assertViewIs('waiver.show');
    $response->assertSee('Jane');
    $response->assertSee('Service Waiver');
});

test('waiver sign records signature and updates customer', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create([
        'name' => 'Jane',
        'email' => 'jane@example.com',
    ]);
    $waiver = $customer->serviceWaiver()->create([
        'expires_at' => now()->addDays(30),
    ]);

    $url = URL::temporarySignedRoute(
        'waiver.show',
        $waiver->expires_at,
        ['customer' => $customer],
        absolute: false
    );

    $response = $this->post($url, [
        '_token' => csrf_token(),
        'signer_name' => 'Jane Doe',
        'signer_email' => 'jane@example.com',
        'agreed' => '1',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $waiver->refresh();
    expect($waiver->signed_at)->not->toBeNull();
    expect($waiver->signer_name)->toBe('Jane Doe');
    expect($waiver->signer_email)->toBe('jane@example.com');

    $customer->refresh();
    expect($customer->waiver_agreed)->toBeTrue();
    expect($customer->waiver_agreed_at)->not->toBeNull();
});

test('waiver show displays already signed when waiver signed', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create(['name' => 'Jane']);
    $waiver = $customer->serviceWaiver()->create([
        'expires_at' => now()->addDays(30),
        'signed_at' => now(),
    ]);

    $url = URL::temporarySignedRoute(
        'waiver.show',
        $waiver->expires_at,
        ['customer' => $customer],
        absolute: false
    );

    $response = $this->get($url);

    $response->assertSuccessful();
    $response->assertViewIs('waiver.already-signed');
    $response->assertSee('Already signed');
});

test('expired signed waiver URL returns 403', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create(['name' => 'Jane']);
    $waiver = $customer->serviceWaiver()->create([
        'expires_at' => now()->subDay(),
    ]);

    $url = URL::temporarySignedRoute(
        'waiver.show',
        $waiver->expires_at,
        ['customer' => $customer],
        absolute: false
    );

    $response = $this->get($url);

    $response->assertForbidden();
});

test('waiver show displays expired when waiver record expired but URL still valid', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create(['name' => 'Jane']);
    $waiver = $customer->serviceWaiver()->create([
        'expires_at' => now()->subDay(),
    ]);

    $url = URL::temporarySignedRoute(
        'waiver.show',
        now()->addDays(1),
        ['customer' => $customer],
        absolute: false
    );

    $response = $this->get($url);

    $response->assertSuccessful();
    $response->assertViewIs('waiver.expired');
    $response->assertSee('expired');
});

test('invalid signature returns 403', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $customer->serviceWaiver()->create(['expires_at' => now()->addDays(30)]);

    $response = $this->get(route('waiver.show', $customer));

    $response->assertForbidden();
});
