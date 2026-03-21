<?php

use App\Enums\CommunicationMethod;
use App\Models\Customer;
use App\Models\Inquiry;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('inquiry index requires authentication', function () {
    $response = $this->get(route('inquiries.index'));

    $response->assertRedirect(route('login'));
});

test('inquiry index lists only own inquiries', function () {
    $user = User::factory()->create();
    Inquiry::factory()->for($user)->count(3)->create();
    Inquiry::factory()->create(); // another user's inquiry

    $response = $this->actingAs($user)->get(route('inquiries.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('inquiries/index')
        ->has('inquiries', 3));
});

test('inquiry create page renders', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('inquiries.create'));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('inquiries/create')
        ->has('communicationMethodOptions', count(CommunicationMethod::cases()))
        ->has('customerOptions'));
});

test('inquiry can be created', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();

    $response = $this->actingAs($user)->post(route('inquiries.store'), [
        'contact_username' => 'john_doe',
        'communication_method' => 'discord',
        'inquired_at' => '2026-03-15',
        'converted' => '1',
        'customer_id' => $customer->id,
        'notes' => 'Interested in card grading',
    ]);

    $response->assertRedirect(route('inquiries.index'));
    $this->assertDatabaseHas('inquiries', [
        'user_id' => $user->id,
        'customer_id' => $customer->id,
        'contact_username' => 'john_doe',
        'communication_method' => 'discord',
        'converted' => true,
        'notes' => 'Interested in card grading',
    ]);
});

test('inquiry can be created without customer', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('inquiries.store'), [
        'contact_username' => 'jane_doe',
        'communication_method' => 'email',
        'inquired_at' => '2026-03-15',
    ]);

    $response->assertRedirect(route('inquiries.index'));
    $this->assertDatabaseHas('inquiries', [
        'user_id' => $user->id,
        'customer_id' => null,
        'contact_username' => 'jane_doe',
        'converted' => false,
    ]);
});

test('inquiry creation validates required fields', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('inquiries.store'), []);

    $response->assertInvalid(['contact_username', 'inquired_at']);
});

test('inquiry creation validates communication method enum', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('inquiries.store'), [
        'contact_username' => 'test_user',
        'inquired_at' => '2026-03-15',
        'communication_method' => 'invalid_method',
    ]);

    $response->assertInvalid(['communication_method']);
});

test('inquiry creation validates customer belongs to user', function () {
    $user = User::factory()->create();
    $otherCustomer = Customer::factory()->create(); // belongs to another user

    $response = $this->actingAs($user)->post(route('inquiries.store'), [
        'contact_username' => 'test_user',
        'inquired_at' => '2026-03-15',
        'customer_id' => $otherCustomer->id,
    ]);

    $response->assertInvalid(['customer_id']);
});

test('inquiry show displays own inquiry', function () {
    $user = User::factory()->create();
    $inquiry = Inquiry::factory()->for($user)->create(['contact_username' => 'display_user']);

    $response = $this->actingAs($user)->get(route('inquiries.show', $inquiry));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('inquiries/show')
        ->where('inquiry.contact_username', 'display_user'));
});

test('inquiry show forbidden for other user', function () {
    $user = User::factory()->create();
    $inquiry = Inquiry::factory()->create();

    $response = $this->actingAs($user)->get(route('inquiries.show', $inquiry));

    $response->assertForbidden();
});

test('inquiry edit page renders', function () {
    $user = User::factory()->create();
    $inquiry = Inquiry::factory()->for($user)->create();

    $response = $this->actingAs($user)->get(route('inquiries.edit', $inquiry));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('inquiries/edit')
        ->has('inquiry')
        ->has('communicationMethodOptions')
        ->has('customerOptions'));
});

test('inquiry edit forbidden for other user', function () {
    $user = User::factory()->create();
    $inquiry = Inquiry::factory()->create();

    $response = $this->actingAs($user)->get(route('inquiries.edit', $inquiry));

    $response->assertForbidden();
});

test('inquiry can be updated', function () {
    $user = User::factory()->create();
    $inquiry = Inquiry::factory()->for($user)->create();

    $response = $this->actingAs($user)->put(route('inquiries.update', $inquiry), [
        'contact_username' => 'updated_user',
        'communication_method' => 'phone',
        'inquired_at' => '2026-03-10',
        'converted' => '1',
    ]);

    $response->assertRedirect(route('inquiries.show', $inquiry));
    $inquiry->refresh();
    expect($inquiry->contact_username)->toBe('updated_user');
    expect($inquiry->communication_method)->toBe(CommunicationMethod::Phone);
    expect($inquiry->converted)->toBeTrue();
});

test('inquiry update forbidden for other user', function () {
    $user = User::factory()->create();
    $inquiry = Inquiry::factory()->create();

    $response = $this->actingAs($user)->put(route('inquiries.update', $inquiry), [
        'contact_username' => 'hacked',
        'inquired_at' => '2026-03-10',
    ]);

    $response->assertForbidden();
});

test('inquiry can be deleted', function () {
    $user = User::factory()->create();
    $inquiry = Inquiry::factory()->for($user)->create();

    $response = $this->actingAs($user)->delete(route('inquiries.destroy', $inquiry));

    $response->assertRedirect(route('inquiries.index'));
    $this->assertDatabaseMissing('inquiries', ['id' => $inquiry->id]);
});

test('inquiry delete forbidden for other user', function () {
    $user = User::factory()->create();
    $inquiry = Inquiry::factory()->create();

    $response = $this->actingAs($user)->delete(route('inquiries.destroy', $inquiry));

    $response->assertForbidden();
    $this->assertDatabaseHas('inquiries', ['id' => $inquiry->id]);
});
