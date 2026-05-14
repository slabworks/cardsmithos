<?php

use App\Models\Card;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Shipment;
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

test('submission index includes required relation counts', function () {
    $user = User::factory()->create();
    $submission = Submission::factory()->for($user)->create();

    Card::factory()->for($submission)->create();
    Payment::factory()->for($submission)->create();
    Shipment::factory()->for($submission)->create();

    $response = $this->actingAs($user)->get(route('submissions.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('submissions/index')
        ->where('submissions.0.cards_count', 1)
        ->where('submissions.0.payments_count', 1)
        ->where('submissions.0.shipments_count', 1));
});

test('submission can be created with an existing customer', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create([
        'name' => 'Jane Doe',
        'contact_detail' => '@janedoe',
    ]);

    $response = $this->actingAs($user)->post(route('submissions.store'), [
        'customer_id' => $customer->id,
        'status' => 'pending',
        'notes' => 'Needs two cards repaired.',
    ]);

    $submission = Submission::query()->where('user_id', $user->id)->first();
    expect($submission)->not->toBeNull();
    $response->assertRedirect(route('submissions.show', $submission));
    $this->assertDatabaseHas('submissions', [
        'id' => $submission->id,
        'customer_id' => $customer->id,
        'status' => 'pending',
        'notes' => 'Needs two cards repaired.',
    ]);
});

test('submission create page can preselect a customer', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();

    $response = $this->actingAs($user)->get(route('submissions.create', [
        'customer_id' => $customer->id,
    ]));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('submissions/create')
        ->where('selectedCustomerId', $customer->id));
});

test('submission cannot be created without a customer', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('submissions.store'), [
        'status' => 'pending',
    ]);

    $response->assertSessionHasErrors('customer_id');
    $this->assertDatabaseCount('submissions', 0);
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
            'status' => 'in_progress',
            'notes' => 'Updated notes',
        ]
    );

    $response->assertRedirect(route('submissions.show', $submission));
    $customer->refresh();
    $submission->refresh();
    expect($submission->customer_id)->toBe($customer->id);
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
