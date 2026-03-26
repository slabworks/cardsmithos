<?php

use App\Models\Card;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('s3');
});

test('photos can be uploaded to a card', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $card = Card::factory()->for($customer)->create();

    $response = $this->actingAs($user)->post(
        route('customers.cards.photos.store', [$customer, $card]),
        ['photos' => [UploadedFile::fake()->image('front.jpg')]]
    );

    $response->assertRedirect();
    expect($card->getMedia('photos'))->toHaveCount(1);
});

test('photo upload requires at least one image', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $card = Card::factory()->for($customer)->create();

    $response = $this->actingAs($user)->post(
        route('customers.cards.photos.store', [$customer, $card]),
        ['photos' => []]
    );

    $response->assertSessionHasErrors('photos');
});

test('photo upload rejects non-image files', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $card = Card::factory()->for($customer)->create();

    $response = $this->actingAs($user)->post(
        route('customers.cards.photos.store', [$customer, $card]),
        ['photos' => [UploadedFile::fake()->create('document.pdf', 100, 'application/pdf')]]
    );

    $response->assertSessionHasErrors('photos.0');
});

test('photo upload forbidden for other users card', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    $card = Card::factory()->for($customer)->create();

    $response = $this->actingAs($user)->post(
        route('customers.cards.photos.store', [$customer, $card]),
        ['photos' => [UploadedFile::fake()->image('front.jpg')]]
    );

    $response->assertForbidden();
});

test('photo can be viewed by card owner', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $card = Card::factory()->for($customer)->create();
    $card->addMedia(UploadedFile::fake()->image('front.jpg'))->toMediaCollection('photos');
    $media = $card->getMedia('photos')->first();

    $response = $this->actingAs($user)->get(
        route('customers.cards.photos.show', [$customer, $card, $media->id])
    );

    $response->assertOk();
});

test('photo view forbidden for other user', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    $card = Card::factory()->for($customer)->create();
    $card->addMedia(UploadedFile::fake()->image('front.jpg'))->toMediaCollection('photos');
    $media = $card->getMedia('photos')->first();

    $response = $this->actingAs($user)->get(
        route('customers.cards.photos.show', [$customer, $card, $media->id])
    );

    $response->assertForbidden();
});

test('photo timeline visibility can be toggled', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $card = Card::factory()->for($customer)->create();
    $card->addMedia(UploadedFile::fake()->image('front.jpg'))->toMediaCollection('photos');
    $media = $card->getMedia('photos')->first();

    expect($media->getCustomProperty('show_on_timeline', false))->toBeFalse();

    $this->actingAs($user)->post(
        route('customers.cards.photos.toggle-timeline', [$customer, $card, $media->id])
    )->assertRedirect();

    $media->refresh();
    expect($media->getCustomProperty('show_on_timeline'))->toBeTrue();

    $this->actingAs($user)->post(
        route('customers.cards.photos.toggle-timeline', [$customer, $card, $media->id])
    )->assertRedirect();

    $media->refresh();
    expect($media->getCustomProperty('show_on_timeline'))->toBeFalse();
});

test('photo toggle timeline forbidden for other user', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    $card = Card::factory()->for($customer)->create();
    $card->addMedia(UploadedFile::fake()->image('front.jpg'))->toMediaCollection('photos');
    $media = $card->getMedia('photos')->first();

    $response = $this->actingAs($user)->post(
        route('customers.cards.photos.toggle-timeline', [$customer, $card, $media->id])
    );

    $response->assertForbidden();
});

test('photo can be deleted', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $card = Card::factory()->for($customer)->create();
    $card->addMedia(UploadedFile::fake()->image('front.jpg'))->toMediaCollection('photos');
    $media = $card->getMedia('photos')->first();

    $response = $this->actingAs($user)->delete(
        route('customers.cards.photos.destroy', [$customer, $card, $media->id])
    );

    $response->assertRedirect();
    expect($card->refresh()->getMedia('photos'))->toHaveCount(0);
});

test('photo delete forbidden for other user', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    $card = Card::factory()->for($customer)->create();
    $card->addMedia(UploadedFile::fake()->image('front.jpg'))->toMediaCollection('photos');
    $media = $card->getMedia('photos')->first();

    $response = $this->actingAs($user)->delete(
        route('customers.cards.photos.destroy', [$customer, $card, $media->id])
    );

    $response->assertForbidden();
});

test('timeline photo accessible with valid share token', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $card = Card::factory()->for($customer)->create();
    $token = $card->ensureTimelineShareToken();
    $card->addMedia(UploadedFile::fake()->image('front.jpg'))->toMediaCollection('photos');
    $media = $card->getMedia('photos')->first();
    $media->setCustomProperty('show_on_timeline', true);
    $media->save();

    $response = $this->get(
        route('card.timeline.photo', [$card, $token, $media->id])
    );

    $response->assertOk();
});

test('timeline photo not accessible with invalid token', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $card = Card::factory()->for($customer)->create();
    $card->ensureTimelineShareToken();
    $card->addMedia(UploadedFile::fake()->image('front.jpg'))->toMediaCollection('photos');
    $media = $card->getMedia('photos')->first();
    $media->setCustomProperty('show_on_timeline', true);
    $media->save();

    $response = $this->get(
        route('card.timeline.photo', [$card, 'invalid-token', $media->id])
    );

    $response->assertNotFound();
});

test('timeline photo not accessible if not marked for timeline', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $card = Card::factory()->for($customer)->create();
    $token = $card->ensureTimelineShareToken();
    $card->addMedia(UploadedFile::fake()->image('front.jpg'))->toMediaCollection('photos');
    $media = $card->getMedia('photos')->first();

    $response = $this->get(
        route('card.timeline.photo', [$card, $token, $media->id])
    );

    $response->assertNotFound();
});
