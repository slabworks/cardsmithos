<?php

use App\Models\Customer;
use App\Models\GmailAccount;
use App\Models\GmailContact;
use App\Models\User;
use App\Services\Gmail\GmailClient;
use App\Services\Gmail\GmailSyncService;

test('gmail sync imports message metadata and links customer', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create(['email' => 'jane@example.com']);
    $account = GmailAccount::query()->create([
        'user_id' => $user->id,
        'email' => 'owner@gmail.com',
        'access_token' => 'token',
        'refresh_token' => 'refresh',
        'token_expires_at' => now()->addHour(),
    ]);

    $client = Mockery::mock(GmailClient::class);
    $client->shouldReceive('getMessageMetadata')
        ->once()
        ->with($account, 'message-1')
        ->andReturn([
            'id' => 'message-1',
            'threadId' => 'thread-1',
            'historyId' => '200',
            'snippet' => 'Can you repair this card?',
            'internalDate' => '1710000000000',
            'payload' => [
                'headers' => [
                    ['name' => 'From', 'value' => 'Jane <jane@example.com>'],
                    ['name' => 'To', 'value' => 'owner@gmail.com'],
                    ['name' => 'Subject', 'value' => 'Repair question'],
                ],
            ],
        ]);

    $contact = (new GmailSyncService($client))->importMessageMetadata($account, 'message-1');

    expect($contact)->toBeInstanceOf(GmailContact::class);
    expect($contact->customer_id)->toBe($customer->id);
    expect($contact->email)->toBe('jane@example.com');
    expect($contact->name)->toBe('Jane');
    expect($contact->latest_subject)->toBe('Repair question');
    expect($contact->latest_snippet)->toBe('Can you repair this card?');
    expect($account->refresh()->history_id)->toBe('200');
});

test('gmail full sync imports contact metadata without fetching full threads', function () {
    config()->set('integrations.gmail.contact_sync_messages', 10);

    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create(['email' => 'jane@example.com']);
    $account = GmailAccount::query()->create([
        'user_id' => $user->id,
        'email' => 'owner@gmail.com',
        'access_token' => 'token',
        'refresh_token' => 'refresh',
        'token_expires_at' => now()->addHour(),
    ]);

    $client = Mockery::mock(GmailClient::class);
    $client->shouldReceive('listMessages')
        ->once()
        ->with($account, null)
        ->andReturn(['messages' => [['id' => 'message-1']]]);
    $client->shouldReceive('getMessageMetadata')
        ->once()
        ->with($account, 'message-1')
        ->andReturn([
            'id' => 'message-1',
            'threadId' => 'thread-1',
            'historyId' => '300',
            'labelIds' => ['INBOX', 'UNREAD'],
            'snippet' => 'It&#39;s ready',
            'internalDate' => '1710000000000',
            'payload' => [
                'headers' => [
                    ['name' => 'From', 'value' => 'Jane <jane@example.com>'],
                    ['name' => 'To', 'value' => 'owner@gmail.com'],
                    ['name' => 'Subject', 'value' => 'Quote &amp; repair'],
                    ['name' => 'Message-ID', 'value' => '<message-1@example.com>'],
                ],
            ],
        ]);
    (new GmailSyncService($client))->fullSync($account);

    $this->assertDatabaseHas('gmail_contacts', [
        'gmail_account_id' => $account->id,
        'customer_id' => $customer->id,
        'email' => 'jane@example.com',
    ]);

    $contact = GmailContact::query()->where('email', 'jane@example.com')->sole();
    expect($contact->latest_subject)->toBe('Quote & repair');
    expect($contact->latest_snippet)->toBe("It's ready");
    expect($account->refresh()->history_id)->toBe('300');
    expect($account->last_synced_at)->not->toBeNull();
});
