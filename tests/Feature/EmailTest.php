<?php

use App\Enums\CustomerStatus;
use App\Models\Customer;
use App\Models\GmailAccount;
use App\Models\GmailContact;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('email index lists only inbound contacts from connected user inbox', function () {
    $user = User::factory()->create();
    $account = GmailAccount::query()->create([
        'user_id' => $user->id,
        'email' => 'owner@gmail.com',
        'access_token' => 'token',
        'refresh_token' => 'refresh',
        'token_expires_at' => now()->addHour(),
    ]);
    GmailContact::query()->create([
        'gmail_account_id' => $account->id,
        'email' => 'jane@example.com',
        'name' => 'Jane Example',
        'latest_subject' => 'Customer question',
        'latest_snippet' => 'Can you repair this card?',
        'latest_gmail_message_id' => 'message-1',
        'latest_gmail_thread_id' => 'thread-1',
        'last_message_at' => now(),
    ]);
    GmailContact::query()->create([
        'gmail_account_id' => GmailAccount::query()->create([
            'user_id' => User::factory()->create()->id,
            'email' => 'other@gmail.com',
            'access_token' => 'token',
            'refresh_token' => 'refresh',
            'token_expires_at' => now()->addHour(),
        ])->id,
        'email' => 'other@example.com',
        'latest_subject' => 'Other question',
        'latest_gmail_message_id' => 'message-2',
        'last_message_at' => now(),
    ]);

    $response = $this->actingAs($user)->get(route('email.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('email/index')
        ->has('contacts', 1)
        ->where('contacts.0.email', 'jane@example.com')
        ->where('contacts.0.name', 'Jane Example')
        ->where('contacts.0.latestMessage.subject', 'Customer question'));
});

test('email contact can be converted to customer', function () {
    $user = User::factory()->create();
    $account = GmailAccount::query()->create([
        'user_id' => $user->id,
        'email' => 'owner@gmail.com',
        'access_token' => 'token',
        'refresh_token' => 'refresh',
        'token_expires_at' => now()->addHour(),
    ]);
    $contact = GmailContact::query()->create([
        'gmail_account_id' => $account->id,
        'email' => 'jane@example.com',
        'name' => 'Jane Example',
        'latest_subject' => 'Repair question',
        'latest_snippet' => 'Can you repair this card?',
        'latest_gmail_message_id' => 'message-1',
        'latest_gmail_thread_id' => 'thread-1',
        'last_message_at' => now(),
    ]);

    $response = $this->actingAs($user)->post(route('email.contacts.convert'), [
        'email' => 'jane@example.com',
    ]);

    $customer = Customer::query()->where('email', 'jane@example.com')->first();
    expect($customer)->not->toBeNull();
    expect($customer->name)->toBe('Jane Example');
    expect($customer->status)->toBe(CustomerStatus::WarmLead);
    $response->assertRedirect(route('customers.show', $customer));
    $this->assertDatabaseHas('gmail_contacts', [
        'id' => $contact->id,
        'customer_id' => $customer->id,
    ]);
});
