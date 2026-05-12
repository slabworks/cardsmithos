<?php

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Submission;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('payment can be created for submission', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();

    $response = $this->actingAs($user)->post(route('submissions.payments.store', $submission), [
        'amount' => 150.00,
        'paid_at' => '2026-03-17',
        'method' => 'card',
        'reference' => 'INV-1001',
    ]);

    $response->assertRedirect(route('submissions.show', $submission));
    $this->assertDatabaseHas('payments', [
        'submission_id' => $submission->id,
        'amount' => 150.00,
        'paid_at' => '2026-03-17 00:00:00',
        'method' => 'card',
        'reference' => 'INV-1001',
    ]);
});

test('submission show includes payment method options', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();

    $response = $this->actingAs($user)->get(route('submissions.show', $submission));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('submissions/show')
        ->where('paymentMethodOptions.0.value', 'e_transfer')
        ->where('paymentMethodOptions.0.label', 'E-transfer'));
});

test('payment creation validates submission ownership', function () {
    $user = User::factory()->create();
    $submission = Submission::factory()->create();

    $response = $this->actingAs($user)->post(route('submissions.payments.store', $submission), [
        'amount' => 150.00,
        'paid_at' => '2026-03-17',
    ]);

    $response->assertForbidden();
});

test('payment can be updated', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();
    $payment = Payment::factory()->for($submission)->create(['amount' => 50]);

    $response = $this->actingAs($user)->put(route('submissions.payments.update', [$submission, $payment]), [
        'amount' => 75.50,
        'paid_at' => '2026-03-18',
        'method' => 'bank_transfer',
        'reference' => 'UPDATED-REF',
    ]);

    $response->assertRedirect(route('submissions.show', $submission));
    $payment->refresh();
    expect((float) $payment->amount)->toBe(75.50);
    expect($payment->method->value)->toBe('bank_transfer');
    expect($payment->reference)->toBe('UPDATED-REF');
});

test('payment method must be a supported option', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();

    $response = $this->actingAs($user)->post(route('submissions.payments.store', $submission), [
        'amount' => 150.00,
        'paid_at' => '2026-03-17',
        'method' => 'wire',
    ]);

    $response->assertSessionHasErrors('method');
});

test('payment can be deleted', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();
    $payment = Payment::factory()->for($submission)->create();

    $response = $this->actingAs($user)->delete(route('submissions.payments.destroy', [$submission, $payment]));

    $response->assertRedirect(route('submissions.show', $submission));
    $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
});
