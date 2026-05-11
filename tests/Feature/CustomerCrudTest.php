<?php

use App\Models\Customer;
use App\Models\GmailAccount;
use App\Models\GmailContact;
use App\Models\Submission;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('submission index lists only own submissions', function () {
    $user = User::factory()->create();
    Submission::factory()->for($user)->count(2)->create();
    Submission::factory()->create();

    $response = $this->actingAs($user)->get(route('submissions.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('submissions/index')
        ->has('submissions', 2));
});

test('submission can be created with a new customer', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('submissions.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'status' => 'warm_lead',
        'notes' => 'Needs two cards repaired.',
    ]);

    $submission = Submission::query()->where('user_id', $user->id)->first();
    expect($submission)->not->toBeNull();
    $response->assertRedirect(route('submissions.show', $submission));
    $this->assertDatabaseHas('customers', [
        'user_id' => $user->id,
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);
    $this->assertDatabaseHas('submissions', [
        'id' => $submission->id,
        'status' => 'warm_lead',
        'notes' => 'Needs two cards repaired.',
    ]);
});

test('creating a submission creates a service waiver', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('submissions.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);

    $submission = Submission::query()->where('user_id', $user->id)->first();
    expect($submission)->not->toBeNull();
    expect($submission->serviceWaiver)->not->toBeNull();
    expect($submission->serviceWaiver->expires_at->isFuture())->toBeTrue();
});

test('submission show displays submission customer', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create(['name' => 'Acme']);
    $submission = Submission::factory()->for($user)->for($customer)->create();

    $response = $this->actingAs($user)->get(route('submissions.show', $submission));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('submissions/show')
        ->where('submission.customer.name', 'Acme'));
});

test('submission show includes linked gmail contacts', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create(['email' => 'jane@example.com']);
    $submission = Submission::factory()->for($user)->for($customer)->create();
    $account = GmailAccount::query()->create([
        'user_id' => $user->id,
        'email' => 'owner@gmail.com',
        'access_token' => 'token',
        'refresh_token' => 'refresh',
        'token_expires_at' => now()->addHour(),
    ]);
    GmailContact::query()->create([
        'gmail_account_id' => $account->id,
        'customer_id' => $customer->id,
        'email' => 'jane@example.com',
        'name' => 'Jane Example',
        'latest_subject' => 'Repair question',
        'latest_snippet' => 'Can you repair this card?',
        'last_message_at' => now(),
    ]);

    $response = $this->actingAs($user)->get(route('submissions.show', $submission));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('submissions/show')
        ->has('emailContacts', 1)
        ->where('emailContacts.0.email', 'jane@example.com')
        ->where('emailContacts.0.latest_subject', 'Repair question'));
});

test('submission show includes waiver URL when waiver not signed', function () {
    $user = User::factory()->create();
    $submission = Submission::factory()->for($user)->create();
    $submission->serviceWaiver()->create([
        'expires_at' => now()->addDays(30),
    ]);

    $response = $this->actingAs($user)->get(route('submissions.show', $submission));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('submissions/show')
        ->where('waiverUrl', fn ($url) => str_contains($url, '/waiver/'.$submission->id) && str_contains($url, 'signature=')));
});

test('submission show forbidden for other user', function () {
    $user = User::factory()->create();
    $submission = Submission::factory()->create();

    $response = $this->actingAs($user)->get(route('submissions.show', $submission));

    $response->assertForbidden();
});

test('submission can be updated', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();

    $response = $this->actingAs($user)->patch(
        route('submissions.update', $submission),
        [
            'customer_id' => $customer->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'status' => 'in_progress',
            'notes' => 'Updated notes',
        ]
    );

    $response->assertRedirect(route('submissions.show', $submission));
    $customer->refresh();
    $submission->refresh();
    expect($customer->name)->toBe('Updated Name');
    expect($customer->email)->toBe('updated@example.com');
    expect($submission->status->value)->toBe('in_progress');
    expect($submission->notes)->toBe('Updated notes');
});

test('submission update forbidden for other user', function () {
    $user = User::factory()->create();
    $submission = Submission::factory()->create();

    $response = $this->actingAs($user)->patch(
        route('submissions.update', $submission),
        ['customer_id' => $submission->customer_id, 'name' => 'Hacked']
    );

    $response->assertForbidden();
});

test('submission can be deleted', function () {
    $user = User::factory()->create();
    $submission = Submission::factory()->for($user)->create();

    $response = $this->actingAs($user)->delete(route('submissions.destroy', $submission));

    $response->assertRedirect(route('submissions.index'));
    $this->assertDatabaseMissing('submissions', ['id' => $submission->id]);
});
