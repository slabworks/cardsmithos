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
        'inquiry_name' => 'John Doe Inquiry',
        'contact_detail' => 'john_doe',
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
        'inquiry_name' => 'John Doe Inquiry',
        'contact_detail' => 'john_doe',
        'communication_method' => 'discord',
        'converted' => true,
        'notes' => 'Interested in card grading',
    ]);
});

test('inquiry can be created without customer', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('inquiries.store'), [
        'inquiry_name' => 'Jane Doe Inquiry',
        'contact_detail' => 'jane_doe',
        'communication_method' => 'email',
        'inquired_at' => '2026-03-15',
    ]);

    $response->assertRedirect(route('inquiries.index'));
    $this->assertDatabaseHas('inquiries', [
        'user_id' => $user->id,
        'customer_id' => null,
        'inquiry_name' => 'Jane Doe Inquiry',
        'contact_detail' => 'jane_doe',
        'converted' => false,
    ]);
});

test('inquiry creation validates required fields', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('inquiries.store'), []);

    $response->assertInvalid(['inquiry_name', 'contact_detail', 'inquired_at']);
});

test('inquiry creation with create_customer creates customer and service waiver', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('inquiries.store'), [
        'inquiry_name' => 'New Lead Inquiry',
        'contact_detail' => 'new_lead',
        'communication_method' => 'email',
        'inquired_at' => '2026-03-15',
        'create_customer' => '1',
    ]);

    $response->assertRedirect(route('inquiries.index'));

    $customer = Customer::where('user_id', $user->id)->where('name', 'New Lead Inquiry')->first();
    expect($customer)->not->toBeNull();

    $this->assertDatabaseHas('inquiries', [
        'user_id' => $user->id,
        'customer_id' => $customer->id,
        'inquiry_name' => 'New Lead Inquiry',
        'converted' => true,
    ]);

    $this->assertDatabaseHas('service_waivers', [
        'customer_id' => $customer->id,
    ]);
});

test('inquiry creation with create_customer passes email to customer', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('inquiries.store'), [
        'inquiry_name' => 'Email Lead',
        'contact_detail' => 'lead@example.com',
        'inquired_at' => '2026-03-15',
        'create_customer' => '1',
    ]);

    $this->assertDatabaseHas('customers', [
        'user_id' => $user->id,
        'name' => 'Email Lead',
        'email' => 'lead@example.com',
    ]);
});

test('inquiry creation with create_customer does not set email for non-email contact', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('inquiries.store'), [
        'inquiry_name' => 'Discord Lead',
        'contact_detail' => 'discord_user#1234',
        'inquired_at' => '2026-03-15',
        'create_customer' => '1',
    ]);

    $this->assertDatabaseHas('customers', [
        'user_id' => $user->id,
        'name' => 'Discord Lead',
        'email' => null,
    ]);
});

test('inquiry creation validates communication method enum', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('inquiries.store'), [
        'inquiry_name' => 'Test Inquiry',
        'contact_detail' => 'test_user',
        'inquired_at' => '2026-03-15',
        'communication_method' => 'invalid_method',
    ]);

    $response->assertInvalid(['communication_method']);
});

test('inquiry creation validates customer belongs to user', function () {
    $user = User::factory()->create();
    $otherCustomer = Customer::factory()->create(); // belongs to another user

    $response = $this->actingAs($user)->post(route('inquiries.store'), [
        'inquiry_name' => 'Test Inquiry',
        'contact_detail' => 'test_user',
        'inquired_at' => '2026-03-15',
        'customer_id' => $otherCustomer->id,
    ]);

    $response->assertInvalid(['customer_id']);
});

test('inquiry show displays own inquiry', function () {
    $user = User::factory()->create();
    $inquiry = Inquiry::factory()->for($user)->create([
        'inquiry_name' => 'Display Inquiry',
        'contact_detail' => 'display_user',
    ]);

    $response = $this->actingAs($user)->get(route('inquiries.show', $inquiry));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('inquiries/show')
        ->where('inquiry.inquiry_name', 'Display Inquiry')
        ->where('inquiry.contact_detail', 'display_user'));
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
        'inquiry_name' => 'Updated Inquiry',
        'contact_detail' => 'updated_user',
        'communication_method' => 'phone',
        'inquired_at' => '2026-03-10',
        'converted' => '1',
    ]);

    $response->assertRedirect(route('inquiries.show', $inquiry));
    $inquiry->refresh();
    expect($inquiry->inquiry_name)->toBe('Updated Inquiry');
    expect($inquiry->contact_detail)->toBe('updated_user');
    expect($inquiry->communication_method)->toBe(CommunicationMethod::Phone);
    expect($inquiry->converted)->toBeTrue();
});

test('inquiry update forbidden for other user', function () {
    $user = User::factory()->create();
    $inquiry = Inquiry::factory()->create();

    $response = $this->actingAs($user)->put(route('inquiries.update', $inquiry), [
        'inquiry_name' => 'Hacked',
        'contact_detail' => 'hacked',
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
