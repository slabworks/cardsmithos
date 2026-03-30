<?php

use App\Models\Customer;
use App\Models\EmailMessage;
use App\Models\GmailAccount;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->gmailAccount = GmailAccount::factory()->create(['user_id' => $this->user->id]);
});

test('email index requires authentication', function () {
    $this->get(route('emails.index'))->assertRedirect('/login');
});

test('email index renders for authenticated user with gmail account', function () {
    $this->actingAs($this->user)
        ->get(route('emails.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('emails/index')
            ->has('hasGmailAccount')
            ->where('hasGmailAccount', true)
        );
});

test('email index shows hasGmailAccount false when not connected', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('emails.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('hasGmailAccount', false));
});

test('email index returns paginated emails for authenticated user', function () {
    EmailMessage::factory()->count(3)->create([
        'user_id' => $this->user->id,
        'gmail_account_id' => $this->gmailAccount->id,
    ]);

    // Other user's email shouldn't appear
    $otherUser = User::factory()->create();
    $otherGmail = GmailAccount::factory()->create(['user_id' => $otherUser->id]);
    EmailMessage::factory()->create([
        'user_id' => $otherUser->id,
        'gmail_account_id' => $otherGmail->id,
    ]);

    $this->actingAs($this->user)
        ->get(route('emails.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('emails.data', 3));
});

test('email index filters by customer', function () {
    $customer = Customer::factory()->create(['user_id' => $this->user->id]);

    EmailMessage::factory()->create([
        'user_id' => $this->user->id,
        'gmail_account_id' => $this->gmailAccount->id,
        'customer_id' => $customer->id,
    ]);

    EmailMessage::factory()->create([
        'user_id' => $this->user->id,
        'gmail_account_id' => $this->gmailAccount->id,
        'customer_id' => null,
    ]);

    $this->actingAs($this->user)
        ->get(route('emails.index', ['customer_id' => $customer->id]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('emails.data', 1));
});

test('email index filters by search query', function () {
    EmailMessage::factory()->create([
        'user_id' => $this->user->id,
        'gmail_account_id' => $this->gmailAccount->id,
        'subject' => 'Pokemon card grading inquiry',
    ]);

    EmailMessage::factory()->create([
        'user_id' => $this->user->id,
        'gmail_account_id' => $this->gmailAccount->id,
        'subject' => 'Unrelated subject line',
    ]);

    $this->actingAs($this->user)
        ->get(route('emails.index', ['search' => 'pokemon']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('emails.data', 1));
});

test('email thread loads when thread_id is provided', function () {
    $threadId = 'thread_123abc';

    EmailMessage::factory()->count(2)->create([
        'user_id' => $this->user->id,
        'gmail_account_id' => $this->gmailAccount->id,
        'gmail_thread_id' => $threadId,
    ]);

    $this->actingAs($this->user)
        ->get(route('emails.index', ['thread_id' => $threadId]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('selectedThread', 2));
});

test('email can be associated with a customer', function () {
    $customer = Customer::factory()->create(['user_id' => $this->user->id]);
    $email = EmailMessage::factory()->create([
        'user_id' => $this->user->id,
        'gmail_account_id' => $this->gmailAccount->id,
        'customer_id' => null,
    ]);

    $this->actingAs($this->user)
        ->post(route('emails.associate', $email), [
            'customer_id' => $customer->id,
        ])
        ->assertRedirect();

    expect($email->fresh()->customer_id)->toBe($customer->id);
});

test('email associate updates all messages in thread', function () {
    $customer = Customer::factory()->create(['user_id' => $this->user->id]);
    $threadId = 'thread_bulk_test';

    $emails = EmailMessage::factory()->count(3)->create([
        'user_id' => $this->user->id,
        'gmail_account_id' => $this->gmailAccount->id,
        'gmail_thread_id' => $threadId,
        'customer_id' => null,
    ]);

    $this->actingAs($this->user)
        ->post(route('emails.associate', $emails->first()), [
            'customer_id' => $customer->id,
        ])
        ->assertRedirect();

    foreach ($emails as $email) {
        expect($email->fresh()->customer_id)->toBe($customer->id);
    }
});

test('email associate forbidden for other user', function () {
    $otherUser = User::factory()->create();
    $otherGmail = GmailAccount::factory()->create(['user_id' => $otherUser->id]);
    $email = EmailMessage::factory()->create([
        'user_id' => $otherUser->id,
        'gmail_account_id' => $otherGmail->id,
    ]);

    $customer = Customer::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->post(route('emails.associate', $email), [
            'customer_id' => $customer->id,
        ])
        ->assertForbidden();
});

test('create inquiry from email', function () {
    $email = EmailMessage::factory()->create([
        'user_id' => $this->user->id,
        'gmail_account_id' => $this->gmailAccount->id,
        'from_name' => 'Ash Ketchum',
        'from_address' => 'ash@pokemon.com',
        'subject' => 'Need Charizard graded',
        'gmail_message_id' => 'msg_inquiry_test',
    ]);

    $this->actingAs($this->user)
        ->post(route('emails.create-inquiry', $email))
        ->assertRedirect();

    $this->assertDatabaseHas('inquiries', [
        'user_id' => $this->user->id,
        'inquiry_name' => 'Ash Ketchum',
        'contact_detail' => 'ash@pokemon.com',
        'communication_method' => 'email',
        'gmail_message_id' => 'msg_inquiry_test',
    ]);
});

test('gmail settings page renders', function () {
    $this->actingAs($this->user)
        ->get(route('gmail.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('settings/gmail'));
});
