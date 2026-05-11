<?php

use App\Models\Card;
use App\Models\Customer;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;

test('invoice create page renders for customer owner', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();
    Card::factory()->for($submission)->count(2)->create();

    $response = $this->actingAs($user)->get(route('submissions.invoices.create', $submission));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('invoices/create')
        ->has('submission')
        ->has('downloadUrl')
        ->has('businessSettings'));
});

test('invoice create page forbidden for non-owner', function () {
    $user = User::factory()->create();
    $submission = Submission::factory()->create();

    $response = $this->actingAs($user)->get(route('submissions.invoices.create', $submission));

    $response->assertForbidden();
});

test('invoice create page includes customer cards', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();
    Card::factory()->for($submission)->count(3)->create();

    $response = $this->actingAs($user)->get(route('submissions.invoices.create', $submission));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('submission.cards', 3));
});

test('invoice create page does not leak sensitive card data', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();
    $card = Card::factory()->for($submission)->create([
        'timeline_share_token' => 'secret-token-value',
        'work_done' => 'Secret work notes',
    ]);

    $response = $this->actingAs($user)->get(route('submissions.invoices.create', $submission));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('submission.cards', 1)
        ->missing('submission.cards.0.timeline_share_token')
        ->missing('submission.cards.0.work_done')
        ->missing('submission.cards.0.photos')
        ->missing('submission.user_id')
        ->missing('submission.customer.email'));
});

test('invoice download returns pdf with valid signed url', function () {
    $user = User::factory()->create();
    $user->businessSettings()->create([
        'hourly_rate' => 50,
        'default_fixed_rate' => 25,
        'tax_rate' => 10,
        'currency' => 'USD',
        'company_name' => 'Test Co',
    ]);
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();
    $card = Card::factory()->for($submission)->create(['restoration_hours' => 2]);

    $downloadUrl = URL::temporarySignedRoute(
        'submissions.invoices.download',
        now()->addHour(),
        ['submission' => $submission],
        absolute: false
    );

    $response = $this->actingAs($user)->post(url($downloadUrl), [
        'card_ids' => [$card->id],
    ]);

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');
});

test('invoice download fails without signed url', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();
    $card = Card::factory()->for($submission)->create();

    $response = $this->actingAs($user)->post(route('submissions.invoices.download', $submission), [
        'card_ids' => [$card->id],
    ]);

    $response->assertForbidden();
});

test('invoice download validates card_ids required', function () {
    $user = User::factory()->create();
    $submission = Submission::factory()->for($user)->create();

    $downloadUrl = URL::temporarySignedRoute(
        'submissions.invoices.download',
        now()->addHour(),
        ['submission' => $submission],
        absolute: false
    );

    $response = $this->actingAs($user)->post(url($downloadUrl), [
        'card_ids' => [],
    ]);

    $response->assertInvalid(['card_ids']);
});

test('invoice download validates cards belong to customer', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();
    $otherSubmission = Submission::factory()->for($user)->create();
    $otherCard = Card::factory()->for($otherSubmission)->create();

    $downloadUrl = URL::temporarySignedRoute(
        'submissions.invoices.download',
        now()->addHour(),
        ['submission' => $submission],
        absolute: false
    );

    $response = $this->actingAs($user)->post(url($downloadUrl), [
        'card_ids' => [$otherCard->id],
    ]);

    $response->assertInvalid(['card_ids.0']);
});

test('invoice download forbidden for non-owner', function () {
    $user = User::factory()->create();
    $submission = Submission::factory()->create();

    $downloadUrl = URL::temporarySignedRoute(
        'submissions.invoices.download',
        now()->addHour(),
        ['submission' => $submission],
        absolute: false
    );

    $response = $this->actingAs($user)->post(url($downloadUrl), [
        'card_ids' => [1],
    ]);

    $response->assertForbidden();
});

test('invoice download includes shipping packaging handling in pdf', function () {
    $user = User::factory()->create();
    $user->businessSettings()->create([
        'hourly_rate' => 50,
        'default_fixed_rate' => 25,
        'tax_rate' => 0,
        'currency' => 'USD',
        'company_name' => 'Test Co',
    ]);
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();
    $card = Card::factory()->for($submission)->create(['restoration_hours' => null]);

    $downloadUrl = URL::temporarySignedRoute(
        'submissions.invoices.download',
        now()->addHour(),
        ['submission' => $submission],
        absolute: false
    );

    $response = $this->actingAs($user)->post(url($downloadUrl), [
        'card_ids' => [$card->id],
        'shipping' => 10.50,
        'packaging' => 5.25,
        'handling' => 3.00,
    ]);

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');
});
