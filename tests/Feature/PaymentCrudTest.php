<?php

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Submission;
use App\Models\User;

test('payment can be created for submission', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();

    $response = $this->actingAs($user)->post(route('submissions.payments.store', $submission), [
        'amount' => 150.00,
        'paid_at' => '2026-03-17',
    ]);

    $response->assertRedirect(route('submissions.show', $submission));
    $this->assertDatabaseHas('payments', [
        'submission_id' => $submission->id,
        'amount' => 150.00,
        'paid_at' => '2026-03-17 00:00:00',
    ]);
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
    ]);

    $response->assertRedirect(route('submissions.show', $submission));
    $payment->refresh();
    expect((float) $payment->amount)->toBe(75.50);
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
