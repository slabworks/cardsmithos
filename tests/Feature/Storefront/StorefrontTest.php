<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('storefront page displays for valid slug', function () {
    $user = User::factory()->create();
    $user->businessSettings()->create([
        'store_slug' => 'test-shop',
        'company_name' => 'Test Shop',
        'hourly_rate' => 50,
        'default_fixed_rate' => 25,
        'currency' => 'USD',
        'bio' => 'We fix cards.',
        'instagram_handle' => 'testshop',
        'tiktok_handle' => 'testshop',
    ]);

    $response = $this->get('/c/test-shop');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('storefront/show')
        ->where('companyName', 'Test Shop')
        ->where('hourlyRate', '50.00')
        ->where('fixedRate', '25.00')
        ->where('currency', 'USD')
        ->where('bio', 'We fix cards.')
        ->where('instagramHandle', 'testshop')
        ->where('tiktokHandle', 'testshop')
    );
});

test('storefront page returns 404 for invalid slug', function () {
    $response = $this->get('/c/nonexistent-shop');

    $response->assertNotFound();
});

test('storefront page handles null optional fields', function () {
    $user = User::factory()->create();
    $user->businessSettings()->create([
        'store_slug' => 'minimal-shop',
        'company_name' => 'Minimal Shop',
    ]);

    $response = $this->get('/c/minimal-shop');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('storefront/show')
        ->where('companyName', 'Minimal Shop')
        ->where('hourlyRate', null)
        ->where('fixedRate', null)
        ->where('bio', null)
        ->where('instagramHandle', null)
        ->where('tiktokHandle', null)
    );
});
