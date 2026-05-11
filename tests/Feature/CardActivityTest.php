<?php

use App\Enums\CardActivityType;
use App\Models\Card;
use App\Models\CardActivity;
use App\Models\Customer;
use App\Models\Submission;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('owner can create card activity', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();
    $card = Card::factory()->for($submission)->create();
    $occurredAt = now()->subDays(2)->format('Y-m-d\TH:i');

    $response = $this->actingAs($user)->post(
        route('submissions.cards.activities.store', [$submission, $card]),
        [
            'type' => CardActivityType::Milestone->value,
            'title' => 'Assessment complete',
            'description' => 'Card condition documented.',
            'occurred_at' => $occurredAt,
        ]
    );

    $response->assertRedirect(route('submissions.cards.edit', [$submission, $card]));
    $response->assertSessionHas('success');
    $this->assertDatabaseHas('card_activities', [
        'card_id' => $card->id,
        'type' => CardActivityType::Milestone->value,
        'title' => 'Assessment complete',
        'description' => 'Card condition documented.',
    ]);
});

test('card activity store forbidden for non-owner', function () {
    $user = User::factory()->create();
    $submission = Submission::factory()->create();
    $card = Card::factory()->for($submission)->create();

    $response = $this->actingAs($user)->post(
        route('submissions.cards.activities.store', [$submission, $card]),
        [
            'type' => CardActivityType::Activity->value,
            'title' => 'Hacked',
            'occurred_at' => now()->format('Y-m-d\TH:i'),
        ]
    );

    $response->assertForbidden();
});

test('owner can update card activity', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();
    $card = Card::factory()->for($submission)->create();
    $activity = CardActivity::factory()->for($card)->create([
        'title' => 'Old title',
        'type' => CardActivityType::Milestone,
    ]);
    $occurredAt = now()->subDay()->format('Y-m-d\TH:i');

    $response = $this->actingAs($user)->put(
        route('submissions.cards.activities.update', [$submission, $card, $activity]),
        [
            'type' => CardActivityType::Activity->value,
            'title' => 'Updated title',
            'description' => 'Updated notes',
            'occurred_at' => $occurredAt,
        ]
    );

    $response->assertRedirect(route('submissions.cards.edit', [$submission, $card]));
    $response->assertSessionHas('success');
    $activity->refresh();
    expect($activity->title)->toBe('Updated title');
    expect($activity->type)->toBe(CardActivityType::Activity);
    expect($activity->description)->toBe('Updated notes');
});

test('card activity update forbidden for non-owner', function () {
    $user = User::factory()->create();
    $submission = Submission::factory()->create();
    $card = Card::factory()->for($submission)->create();
    $activity = CardActivity::factory()->for($card)->create();

    $response = $this->actingAs($user)->put(
        route('submissions.cards.activities.update', [$submission, $card, $activity]),
        [
            'type' => CardActivityType::Milestone->value,
            'title' => 'Hacked',
            'occurred_at' => now()->format('Y-m-d\TH:i'),
        ]
    );

    $response->assertForbidden();
});

test('owner can delete card activity', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();
    $card = Card::factory()->for($submission)->create();
    $activity = CardActivity::factory()->for($card)->create();

    $response = $this->actingAs($user)->delete(
        route('submissions.cards.activities.destroy', [$submission, $card, $activity])
    );

    $response->assertRedirect(route('submissions.cards.edit', [$submission, $card]));
    $response->assertSessionHas('success');
    $this->assertDatabaseMissing('card_activities', ['id' => $activity->id]);
});

test('card activity destroy forbidden for non-owner', function () {
    $user = User::factory()->create();
    $submission = Submission::factory()->create();
    $card = Card::factory()->for($submission)->create();
    $activity = CardActivity::factory()->for($card)->create();

    $response = $this->actingAs($user)->delete(
        route('submissions.cards.activities.destroy', [$submission, $card, $activity])
    );

    $response->assertForbidden();
});

test('public timeline returns 200 and card data when token is valid', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create(['name' => 'Jane']);
    $submission = Submission::factory()->for($user)->for($customer)->create();
    $card = Card::factory()->for($submission)->create([
        'name' => 'Pikachu',
        'timeline_share_token' => 'valid-token-123',
    ]);
    CardActivity::factory()->for($card)->create([
        'title' => 'First milestone',
        'type' => CardActivityType::Milestone,
    ]);

    $response = $this->get(route('card.timeline.show', ['card' => $card, 'token' => 'valid-token-123']));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('cards/timeline-public')
        ->has('card')
        ->where('card.name', 'Pikachu')
        ->where('card.submission.customer.name', 'Jane')
        ->has('card.activities', 1)
        ->where('card.activities.0.title', 'First milestone')
    );
});

test('public timeline returns 404 when token is invalid', function () {
    $user = User::factory()->create();
    $submission = Submission::factory()->for($user)->create();
    $card = Card::factory()->for($submission)->create([
        'timeline_share_token' => 'correct-token',
    ]);

    $response = $this->get(route('card.timeline.show', ['card' => $card, 'token' => 'wrong-token']));

    $response->assertNotFound();
});

test('public timeline returns 404 when token is empty', function () {
    $user = User::factory()->create();
    $submission = Submission::factory()->for($user)->create();
    $card = Card::factory()->for($submission)->create([
        'timeline_share_token' => 'secret',
    ]);

    $response = $this->get('/cards/'.$card->id.'/timeline/');

    $response->assertNotFound();
});

test('owner can rotate timeline share token and old url returns 404', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();
    $card = Card::factory()->for($submission)->create([
        'timeline_share_token' => 'old-token',
    ]);

    $response = $this->actingAs($user)->post(
        route('submissions.cards.timeline.rotate-token', [$submission, $card])
    );

    $response->assertRedirect(route('submissions.cards.edit', [$submission, $card]));
    $response->assertSessionHas('success');

    $card->refresh();
    expect($card->timeline_share_token)->not->toBe('old-token');

    $oldUrlResponse = $this->get(route('card.timeline.show', ['card' => $card, 'token' => 'old-token']));
    $oldUrlResponse->assertNotFound();
});

test('timeline rotate token forbidden for non-owner', function () {
    $user = User::factory()->create();
    $submission = Submission::factory()->create();
    $card = Card::factory()->for($submission)->create(['timeline_share_token' => 'token']);

    $response = $this->actingAs($user)->post(
        route('submissions.cards.timeline.rotate-token', [$submission, $card])
    );

    $response->assertForbidden();
});
