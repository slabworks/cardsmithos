<?php

use App\Models\BusinessSettings;
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

test('storefront show page includes location data', function () {
    $user = User::factory()->create();
    $user->businessSettings()->create([
        'store_slug' => 'located-shop',
        'company_name' => 'Located Shop',
        'country' => 'US',
        'location_name' => null,
    ]);

    $response = $this->get('/c/located-shop');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('storefront/show')
        ->where('country', 'US')
        ->where('locationName', null)
    );
});

test('storefront show page passes hidePricing prop and contact email', function () {
    $user = User::factory()->create(['email' => 'shop@example.com']);
    $user->businessSettings()->create([
        'store_slug' => 'hidden-pricing-shop',
        'company_name' => 'Hidden Pricing Shop',
        'hourly_rate' => 50,
        'hide_pricing' => true,
    ]);

    $response = $this->get('/c/hidden-pricing-shop');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('storefront/show')
        ->where('hidePricing', true)
        ->where('hourlyRate', '50.00')
        ->where('contactEmail', 'shop@example.com')
    );
});

test('storefront show page does not expose contact email when pricing is visible', function () {
    $user = User::factory()->create(['email' => 'shop@example.com']);
    $user->businessSettings()->create([
        'store_slug' => 'visible-pricing-shop',
        'company_name' => 'Visible Pricing Shop',
        'hourly_rate' => 50,
    ]);

    $response = $this->get('/c/visible-pricing-shop');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('storefront/show')
        ->where('hidePricing', false)
        ->where('contactEmail', null)
    );
});

test('directory page includes hide_pricing in storefront data', function () {
    BusinessSettings::factory()->create([
        'hide_pricing' => true,
    ]);

    $response = $this->get(route('storefront.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->has('storefronts.data', 1)
        ->where('storefronts.data.0.hide_pricing', true)
    );
});

test('directory page loads successfully', function () {
    $response = $this->get(route('storefront.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('storefront/index')
        ->has('storefronts')
        ->has('totalStorefronts')
        ->has('availableCountries')
        ->has('filters')
    );
});

test('directory page lists storefronts with slugs', function () {
    BusinessSettings::factory()->count(3)->create();

    $response = $this->get(route('storefront.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('storefront/index')
        ->where('totalStorefronts', 3)
        ->has('storefronts.data', 3)
    );
});

test('directory page excludes storefronts without slugs', function () {
    BusinessSettings::factory()->count(2)->create();
    BusinessSettings::factory()->withoutSlug()->create();

    $response = $this->get(route('storefront.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->where('totalStorefronts', 2)
        ->has('storefronts.data', 2)
    );
});

test('directory page excludes de-listed storefronts', function () {
    BusinessSettings::factory()->count(2)->create();
    BusinessSettings::factory()->create([
        'is_listed_in_directory' => false,
    ]);

    $response = $this->get(route('storefront.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->where('totalStorefronts', 2)
        ->has('storefronts.data', 2)
    );
});

test('directory page filters by search term', function () {
    BusinessSettings::factory()->create(['company_name' => 'Alpha Cards']);
    BusinessSettings::factory()->create(['company_name' => 'Beta Repairs']);

    $response = $this->get(route('storefront.index', ['search' => 'Alpha']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->has('storefronts.data', 1)
        ->where('storefronts.data.0.company_name', 'Alpha Cards')
    );
});

test('directory page filters by country', function () {
    BusinessSettings::factory()->create(['country' => 'US']);
    BusinessSettings::factory()->create(['country' => 'JP']);
    BusinessSettings::factory()->create(['country' => 'US']);

    $response = $this->get(route('storefront.index', ['countries' => 'US']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->has('storefronts.data', 2)
    );
});

test('directory page returns available countries', function () {
    BusinessSettings::factory()->create(['country' => 'US']);
    BusinessSettings::factory()->create(['country' => 'JP']);
    BusinessSettings::factory()->otherCountry()->create();

    $response = $this->get(route('storefront.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->where('availableCountries', ['JP', 'US'])
    );
});

test('directory page sorts by name by default', function () {
    BusinessSettings::factory()->create(['company_name' => 'Zebra Cards']);
    BusinessSettings::factory()->create(['company_name' => 'Alpha Cards']);

    $response = $this->get(route('storefront.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->where('storefronts.data.0.company_name', 'Alpha Cards')
        ->where('storefronts.data.1.company_name', 'Zebra Cards')
    );
});

test('directory page de-listed shops are not counted in totalStorefronts', function () {
    BusinessSettings::factory()->count(3)->create();
    BusinessSettings::factory()->create(['is_listed_in_directory' => false]);

    $response = $this->get(route('storefront.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->where('totalStorefronts', 3)
    );
});
