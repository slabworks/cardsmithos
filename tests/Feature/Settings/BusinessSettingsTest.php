<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('business settings page is displayed and creates default settings', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('business.edit'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('settings/business')
        ->has('businessSettings')
    );

    $this->assertDatabaseHas('business_settings', [
        'user_id' => $user->id,
    ]);
});

test('business settings can be updated', function () {
    $user = User::factory()->create();
    $user->businessSettings()->create([
        'hourly_rate' => 100,
        'currency' => 'USD',
    ]);

    $response = $this->actingAs($user)->patch(route('business.update'), [
        'hourly_rate' => 125.50,
        'default_fixed_rate' => 50,
        'currency' => 'USD',
        'company_name' => 'Acme Restorations',
        'tax_rate' => 8.5,
        'store_slug' => 'acme-cards',
        'bio' => 'We restore cards.',
        'instagram_handle' => 'acmecards',
        'tiktok_handle' => 'acmecards',
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('business.edit'));

    $user->businessSettings->refresh();
    expect((float) $user->businessSettings->hourly_rate)->toBe(125.50);
    expect((float) $user->businessSettings->default_fixed_rate)->toBe(50.0);
    expect($user->businessSettings->company_name)->toBe('Acme Restorations');
    expect((float) $user->businessSettings->tax_rate)->toBe(8.5);
    expect($user->businessSettings->store_slug)->toBe('acme-cards');
    expect($user->businessSettings->bio)->toBe('We restore cards.');
    expect($user->businessSettings->instagram_handle)->toBe('acmecards');
    expect($user->businessSettings->tiktok_handle)->toBe('acmecards');
});

test('business settings can update country and location name', function () {
    $user = User::factory()->create();
    $user->businessSettings()->create([
        'hourly_rate' => 100,
        'currency' => 'USD',
    ]);

    $response = $this->actingAs($user)->patch(route('business.update'), [
        'country' => 'OT',
        'location_name' => 'Singapore',
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('business.edit'));

    $user->businessSettings->refresh();
    expect($user->businessSettings->country)->toBe('OT');
    expect($user->businessSettings->location_name)->toBe('Singapore');
});

test('business settings can update is_listed_in_directory', function () {
    $user = User::factory()->create();
    $user->businessSettings()->create([
        'hourly_rate' => 100,
        'currency' => 'USD',
        'store_slug' => 'test-shop',
        'is_listed_in_directory' => true,
    ]);

    $response = $this->actingAs($user)->patch(route('business.update'), [
        'is_listed_in_directory' => false,
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('business.edit'));

    $user->businessSettings->refresh();
    expect($user->businessSettings->is_listed_in_directory)->toBeFalse();
});

test('is_listed_in_directory defaults to true for new settings', function () {
    $user = User::factory()->create();
    $user->businessSettings()->create([
        'hourly_rate' => 100,
        'currency' => 'USD',
    ]);

    expect($user->businessSettings->is_listed_in_directory)->toBeTrue();
});
