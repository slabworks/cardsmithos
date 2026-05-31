<?php

use App\Models\Card;
use App\Models\Customer;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    config(['cardsmithos.photos_enabled' => true]);
    Storage::fake(config('media-library.disk_name'));
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

test('photo routes return not found when photos are disabled', function () {
    config(['cardsmithos.photos_enabled' => false]);

    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();
    $card = Card::factory()->for($submission)->create();
    $card->addMedia(UploadedFile::fake()->image('front.jpg'))->toMediaCollection('photos');
    $media = $card->getMedia('photos')->first();

    $this->actingAs($user)->post(
        route('submissions.cards.photos.store', [$submission, $card]),
        ['photos' => [UploadedFile::fake()->image('front.jpg')]]
    )->assertNotFound();

    $this->actingAs($user)->get(
        route('submissions.cards.photos.show', [$submission, $card, $media->id])
    )->assertNotFound();

    $this->actingAs($user)->delete(
        route('submissions.cards.photos.destroy', [$submission, $card, $media->id])
    )->assertNotFound();
});

test('card edit page hides photos when photos are disabled', function () {
    config(['cardsmithos.photos_enabled' => false]);

    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $submission = Submission::factory()->for($user)->for($customer)->create();
    $card = Card::factory()->for($submission)->create();

    $response = $this->actingAs($user)->get(
        route('submissions.cards.edit', [$submission, $card])
    );

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('cards/edit')
        ->where('photosEnabled', false)
        ->has('photos', 0));
});
