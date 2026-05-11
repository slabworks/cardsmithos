<?php

use App\Models\Customer;
use App\Models\Shipment;
use App\Models\Submission;
use App\Models\User;

test('shipment can be created for submission', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();

    $response = $this->actingAs($user)->post(route('submissions.shipments.store', $submission), [
        'amount' => 18.75,
        'shipped_at' => '2026-03-19',
        'tracking_number' => '1Z999AA10123456784',
        'reference' => 'RETURN-LABEL',
    ]);

    $response->assertRedirect(route('submissions.show', $submission));
    $this->assertDatabaseHas('shipments', [
        'submission_id' => $submission->id,
        'amount' => 18.75,
        'shipped_at' => '2026-03-19 00:00:00',
        'tracking_number' => '1Z999AA10123456784',
        'reference' => 'RETURN-LABEL',
    ]);
});

test('shipment creation validates submission ownership', function () {
    $user = User::factory()->create();
    $submission = Submission::factory()->create();

    $response = $this->actingAs($user)->post(route('submissions.shipments.store', $submission), [
        'amount' => 18.75,
        'shipped_at' => '2026-03-19',
    ]);

    $response->assertForbidden();
});

test('shipment can be updated', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();
    $shipment = Shipment::factory()->for($submission)->create();

    $response = $this->actingAs($user)->put(route('submissions.shipments.update', [$submission, $shipment]), [
        'amount' => 25.00,
        'shipped_at' => '2026-03-20',
        'tracking_number' => 'UPDATED-TRACKING',
        'reference' => 'UPDATED-REF',
    ]);

    $response->assertRedirect(route('submissions.show', $submission));
    $shipment->refresh();
    expect((float) $shipment->amount)->toBe(25.00);
    expect($shipment->tracking_number)->toBe('UPDATED-TRACKING');
});

test('shipment can be deleted', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();
    $shipment = Shipment::factory()->for($submission)->create();

    $response = $this->actingAs($user)->delete(route('submissions.shipments.destroy', [$submission, $shipment]));

    $response->assertRedirect(route('submissions.show', $submission));
    $this->assertDatabaseMissing('shipments', ['id' => $shipment->id]);
});
