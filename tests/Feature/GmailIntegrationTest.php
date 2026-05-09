<?php

use App\Jobs\SyncGmailAccount;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;

test('integrations page shows gmail connection state', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('integrations.edit'));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('settings/integrations')
        ->where('gmail.connected', false));
});

test('gmail redirect starts oauth flow with state', function () {
    config()->set('integrations.gmail.client_id', 'client-id');
    config()->set('integrations.gmail.client_secret', 'client-secret');
    config()->set('integrations.gmail.redirect_uri', 'https://cardsmithos.test/settings/integrations/gmail/callback');
    config()->set('integrations.gmail.scopes', ['https://www.googleapis.com/auth/gmail.readonly']);

    $user = User::factory()->create(['email' => 'owner@example.com']);

    $response = $this->actingAs($user)->get(route('integrations.gmail.redirect'));

    $response->assertRedirect();
    expect($response->headers->get('Location'))
        ->toContain('https://accounts.google.com/o/oauth2/v2/auth')
        ->toContain('client_id=client-id')
        ->toContain('scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fgmail.readonly')
        ->toContain('include_granted_scopes=false')
        ->not->toContain('gmail.compose')
        ->not->toContain('gmail.modify');
    expect(session('gmail_oauth_state'))->not->toBeNull();
});

test('gmail callback stores account and queues initial sync', function () {
    Queue::fake();
    Http::fake([
        'oauth2.googleapis.com/token' => Http::response([
            'access_token' => 'access-token',
            'refresh_token' => 'refresh-token',
            'expires_in' => 3600,
            'scope' => 'https://www.googleapis.com/auth/gmail.readonly',
        ]),
        'gmail.googleapis.com/gmail/v1/users/me/profile' => Http::response([
            'emailAddress' => 'owner@gmail.com',
            'historyId' => '123',
        ]),
    ]);
    config()->set('integrations.gmail.client_id', 'client-id');
    config()->set('integrations.gmail.client_secret', 'client-secret');
    config()->set('integrations.gmail.redirect_uri', 'https://cardsmithos.test/settings/integrations/gmail/callback');

    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['gmail_oauth_state' => 'state-value'])
        ->get(route('integrations.gmail.callback', ['state' => 'state-value', 'code' => 'code-value']));

    $response->assertRedirect(route('integrations.edit'));
    $this->assertDatabaseHas('gmail_accounts', [
        'user_id' => $user->id,
        'email' => 'owner@gmail.com',
        'history_id' => '123',
    ]);
    Queue::assertPushed(SyncGmailAccount::class);
});
