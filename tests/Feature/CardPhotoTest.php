<?php

use App\Models\Card;
use App\Models\Customer;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('s3');
});

test('photos can be uploaded to a card', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();
    $card = Card::factory()->for($submission)->create();

    $response = $this->actingAs($user)->post(
        route('submissions.cards.photos.store', [$submission, $card]),
        ['photos' => [UploadedFile::fake()->image('front.jpg')]]
    );

    $response->assertRedirect();
    expect($card->getMedia('photos'))->toHaveCount(1);
});

test('photo upload requires at least one image', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();
    $card = Card::factory()->for($submission)->create();

    $response = $this->actingAs($user)->post(
        route('submissions.cards.photos.store', [$submission, $card]),
        ['photos' => []]
    );

    $response->assertSessionHasErrors('photos');
});

test('photo upload rejects non-image files', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();
    $card = Card::factory()->for($submission)->create();

    $response = $this->actingAs($user)->post(
        route('submissions.cards.photos.store', [$submission, $card]),
        ['photos' => [UploadedFile::fake()->create('document.pdf', 100, 'application/pdf')]]
    );

    $response->assertSessionHasErrors('photos.0');
});

test('photo upload forbidden for other users card', function () {
    $user = User::factory()->create();
    $submission = Submission::factory()->create();
    $card = Card::factory()->for($submission)->create();

    $response = $this->actingAs($user)->post(
        route('submissions.cards.photos.store', [$submission, $card]),
        ['photos' => [UploadedFile::fake()->image('front.jpg')]]
    );

    $response->assertForbidden();
});

test('photo can be viewed by card owner', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();
    $card = Card::factory()->for($submission)->create();
    $card->addMedia(UploadedFile::fake()->image('front.jpg'))->toMediaCollection('photos');
    $media = $card->getMedia('photos')->first();

    $response = $this->actingAs($user)->get(
        route('submissions.cards.photos.show', [$submission, $card, $media->id])
    );

    $response->assertOk();
});

test('photo view forbidden for other user', function () {
    $user = User::factory()->create();
    $submission = Submission::factory()->create();
    $card = Card::factory()->for($submission)->create();
    $card->addMedia(UploadedFile::fake()->image('front.jpg'))->toMediaCollection('photos');
    $media = $card->getMedia('photos')->first();

    $response = $this->actingAs($user)->get(
        route('submissions.cards.photos.show', [$submission, $card, $media->id])
    );

    $response->assertForbidden();
});

test('photo can be deleted', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();
    $card = Card::factory()->for($submission)->create();
    $card->addMedia(UploadedFile::fake()->image('front.jpg'))->toMediaCollection('photos');
    $media = $card->getMedia('photos')->first();

    $response = $this->actingAs($user)->delete(
        route('submissions.cards.photos.destroy', [$submission, $card, $media->id])
    );

    $response->assertRedirect();
    expect($card->refresh()->getMedia('photos'))->toHaveCount(0);
});

test('photo delete forbidden for other user', function () {
    $user = User::factory()->create();
    $submission = Submission::factory()->create();
    $card = Card::factory()->for($submission)->create();
    $card->addMedia(UploadedFile::fake()->image('front.jpg'))->toMediaCollection('photos');
    $media = $card->getMedia('photos')->first();

    $response = $this->actingAs($user)->delete(
        route('submissions.cards.photos.destroy', [$submission, $card, $media->id])
    );

    $response->assertForbidden();
});
