<?php

use App\Enums\CardCondition;
use App\Models\Card;
use App\Models\Customer;
use App\Models\Submission;
use App\Models\User;

test('card can be created for customer', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();

    $response = $this->actingAs($user)->post(
        route('submissions.cards.store', $submission),
        [
            'name' => 'Charizard',
            'status' => 'pending',
            'condition' => CardCondition::NearMint->value,
        ]
    );

    $response->assertRedirect(route('submissions.show', $submission));
    $this->assertDatabaseHas('cards', [
        'submission_id' => $submission->id,
        'name' => 'Charizard',
        'status' => 'pending',
        'condition' => CardCondition::NearMint->value,
    ]);
});

test('card store forbidden for other users customer', function () {
    $user = User::factory()->create();
    $submission = Submission::factory()->create();

    $response = $this->actingAs($user)->post(
        route('submissions.cards.store', $submission),
        ['name' => 'Card', 'status' => 'pending']
    );

    $response->assertForbidden();
});

test('card estimated_fee is computed from restoration_hours and business hourly rate', function () {
    $user = User::factory()->create();
    $user->businessSettings()->create(['hourly_rate' => 100, 'currency' => 'USD']);
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();

    $this->actingAs($user)->post(
        route('submissions.cards.store', $submission),
        [
            'name' => 'Test Card',
            'status' => 'pending',
            'restoration_hours' => 2.5,
        ]
    );

    $card = Card::where('submission_id', $submission->id)->first();
    expect((float) $card->estimated_fee)->toBe(250.0); // 2.5 * 100
});

test('card estimated_fee uses user business settings hourly rate when set', function () {
    $user = User::factory()->create();
    $user->businessSettings()->create(['hourly_rate' => 80, 'currency' => 'USD']);
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();

    $this->actingAs($user)->post(
        route('submissions.cards.store', $submission),
        [
            'name' => 'Test Card',
            'status' => 'pending',
            'restoration_hours' => 2.5,
        ]
    );

    $card = Card::where('submission_id', $submission->id)->first();
    expect((float) $card->estimated_fee)->toBe(200.0); // 2.5 * 80
});

test('card can be updated', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();
    $card = Card::factory()->for($submission)->create(['name' => 'Old Name']);

    $response = $this->actingAs($user)->patch(
        route('submissions.cards.update', [$submission, $card]),
        ['name' => 'New Name', 'status' => 'in_progress', 'condition' => CardCondition::Damaged->value]
    );

    $response->assertRedirect(route('submissions.show', $submission));
    $card->refresh();
    expect($card->name)->toBe('New Name');
    expect($card->status->value)->toBe('in_progress');
    expect($card->condition)->toBe(CardCondition::Damaged);
});

test('card update forbidden for other user', function () {
    $user = User::factory()->create();
    $submission = Submission::factory()->create();
    $card = Card::factory()->for($submission)->create();

    $response = $this->actingAs($user)->patch(
        route('submissions.cards.update', [$submission, $card]),
        ['name' => 'Hacked', 'status' => 'pending']
    );

    $response->assertForbidden();
});

test('card can be deleted', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();
    $card = Card::factory()->for($submission)->create();

    $response = $this->actingAs($user)->delete(
        route('submissions.cards.destroy', [$submission, $card])
    );

    $response->assertRedirect(route('submissions.show', $submission));
    $this->assertDatabaseMissing('cards', ['id' => $card->id]);
});
