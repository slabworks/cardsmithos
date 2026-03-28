<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Jobs\SyncGmailMessages;
use App\Models\GmailAccount;
use Google\Client as GoogleClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GmailConnectionController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/gmail', [
            'gmailAccount' => $request->user()->gmailAccount?->only('email', 'last_synced_at'),
        ]);
    }

    public function connect(Request $request): RedirectResponse
    {
        $client = $this->getGoogleClient();

        $state = bin2hex(random_bytes(16));
        $request->session()->put('gmail_oauth_state', $state);
        $client->setState($state);

        return redirect()->away($client->createAuthUrl());
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->has('error')) {
            return to_route('gmail.edit')->with('error', 'Gmail connection was cancelled.');
        }

        $expectedState = $request->session()->pull('gmail_oauth_state');
        if (! $expectedState || ! hash_equals($expectedState, $request->query('state', ''))) {
            return to_route('gmail.edit')->with('error', 'Invalid OAuth state. Please try again.');
        }

        $client = $this->getGoogleClient();
        $token = $client->fetchAccessTokenWithAuthCode($request->query('code'));

        if (isset($token['error'])) {
            return to_route('gmail.edit')->with('error', 'Failed to connect Gmail: '.$token['error_description']);
        }

        $client->setAccessToken($token);
        $oauth2 = new \Google\Service\Oauth2($client);
        $googleUser = $oauth2->userinfo->get();

        $attributes = [
            'google_id' => $googleUser->id,
            'email' => $googleUser->email,
            'access_token' => $token['access_token'],
            'token_expires_at' => now()->addSeconds($token['expires_in']),
        ];

        if (! empty($token['refresh_token'])) {
            $attributes['refresh_token'] = $token['refresh_token'];
        }

        $gmailAccount = $request->user()->gmailAccount()->updateOrCreate([], $attributes);

        SyncGmailMessages::dispatch($gmailAccount->id);

        return to_route('gmail.edit')->with('success', 'Gmail connected successfully. Emails are syncing in the background.');
    }

    public function disconnect(Request $request): RedirectResponse
    {
        $gmailAccount = $request->user()->gmailAccount;

        if ($gmailAccount) {
            try {
                $client = $this->getGoogleClient();
                $client->setAccessToken($gmailAccount->access_token);
                $client->revokeToken();
            } catch (\Exception) {
                // Token may already be invalid — continue with deletion
            }

            $gmailAccount->delete();
        }

        return to_route('gmail.edit')->with('success', 'Gmail disconnected.');
    }

    private function getGoogleClient(): GoogleClient
    {
        $client = new GoogleClient;
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect_uri'));
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setScopes([
            'https://www.googleapis.com/auth/gmail.readonly',
            'https://www.googleapis.com/auth/gmail.send',
            'https://www.googleapis.com/auth/gmail.modify',
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile',
        ]);

        return $client;
    }
}
