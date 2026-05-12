<?php

use App\Models\Customer;
use App\Models\ServiceWaiver;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\URL;

test('waiver index lists own waivers', function () {
    $user = User::factory()->create();
    ServiceWaiver::factory()->for($user)->count(2)->create();
    ServiceWaiver::factory()->create();

    $response = $this->actingAs($user)->get(route('waivers.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('waivers/index')
        ->has('waivers', 2));
});

test('can create a standalone waiver', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('waivers.store'));

    $response->assertRedirect(route('waivers.index'));
    expect($user->serviceWaivers()->latest()->first())->not->toBeNull()
        ->and($user->serviceWaivers()->latest()->first()->expires_at->isFuture())->toBeTrue();
});

test('can delete an unsigned waiver', function () {
    $user = User::factory()->create();
    $waiver = ServiceWaiver::factory()->for($user)->create();

    $response = $this->actingAs($user)->delete(route('waivers.destroy', $waiver));

    $response->assertRedirect(route('waivers.index'));
    expect(ServiceWaiver::whereKey($waiver->id)->exists())->toBeFalse();
});

test('cannot delete another user\'s waiver', function () {
    $user = User::factory()->create();
    $waiver = ServiceWaiver::factory()->create();

    $response = $this->actingAs($user)->delete(route('waivers.destroy', $waiver));

    $response->assertForbidden();
});

test('signed waiver URL shows waiver form when not signed', function () {
    $waiver = ServiceWaiver::factory()->create([
        'expires_at' => now()->addDays(30),
    ]);

    $url = URL::temporarySignedRoute(
        'waiver.show',
        $waiver->expires_at,
        ['serviceWaiver' => $waiver],
        absolute: false
    );

    $response = $this->get($url);

    $response->assertSuccessful();
    $response->assertViewIs('waiver.show');
    $response->assertSee('Service Waiver');
});

test('waiver sign records signature', function () {
    $waiver = ServiceWaiver::factory()->create([
        'expires_at' => now()->addDays(30),
    ]);

    $url = URL::temporarySignedRoute(
        'waiver.show',
        $waiver->expires_at,
        ['serviceWaiver' => $waiver],
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
});

test('waiver show displays already signed when waiver signed', function () {
    $waiver = ServiceWaiver::factory()->create([
        'expires_at' => now()->addDays(30),
        'signed_at' => now(),
        'signer_name' => 'Jane',
    ]);

    $url = URL::temporarySignedRoute(
        'waiver.show',
        $waiver->expires_at,
        ['serviceWaiver' => $waiver],
        absolute: false
    );

    $response = $this->get($url);

    $response->assertSuccessful();
    $response->assertViewIs('waiver.already-signed');
    $response->assertSee('Already signed');
});

test('waiver show displays expired view when waiver record expired', function () {
    $waiver = ServiceWaiver::factory()->create([
        'expires_at' => now()->subDay(),
    ]);

    $url = URL::temporarySignedRoute(
        'waiver.show',
        now()->addDays(1),
        ['serviceWaiver' => $waiver],
        absolute: false
    );

    $response = $this->get($url);

    $response->assertSuccessful();
    $response->assertViewIs('waiver.expired');
    $response->assertSee('expired');
});

test('expired signed waiver URL returns 403', function () {
    $waiver = ServiceWaiver::factory()->create([
        'expires_at' => now()->addDays(30),
    ]);

    $url = URL::temporarySignedRoute(
        'waiver.show',
        now()->subSecond(),
        ['serviceWaiver' => $waiver],
        absolute: false
    );

    $response = $this->get($url);

    $response->assertForbidden();
});

test('invalid signature returns 403', function () {
    $waiver = ServiceWaiver::factory()->create(['expires_at' => now()->addDays(30)]);

    $response = $this->get(route('waiver.show', $waiver));

    $response->assertForbidden();
});

test('submission show does not include waiver URL', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create(['name' => 'Acme']);
    $submission = Submission::factory()->for($user)->for($customer)->create();

    $response = $this->actingAs($user)->get(route('submissions.show', $submission));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('submissions/show')
        ->where('submission.customer.name', 'Acme'));
});

test('creating a submission does not create a service waiver', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();

    $this->actingAs($user)->post(route('submissions.store'), [
        'customer_id' => $customer->id,
        'status' => 'pending',
    ]);

    $submission = Submission::query()->where('user_id', $user->id)->first();
    expect($submission)->not->toBeNull()
        ->and(ServiceWaiver::query()->count())->toBe(0);
});
