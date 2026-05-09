<?php

namespace App\Services\Gmail;

use App\Models\GmailAccount;
use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GmailOAuthService
{
    public function authorizationUrl(User $user, string $state): string
    {
        return 'https://accounts.google.com/o/oauth2/v2/auth?'.Arr::query([
            'client_id' => config('integrations.gmail.client_id'),
            'redirect_uri' => config('integrations.gmail.redirect_uri'),
            'response_type' => 'code',
            'scope' => implode(' ', config('integrations.gmail.scopes', [])),
            'access_type' => 'offline',
            'include_granted_scopes' => 'false',
            'prompt' => 'consent',
            'login_hint' => $user->email,
            'state' => $state,
        ]);
    }

    public function connect(User $user, string $code): GmailAccount
    {
        $token = Http::asForm()
            ->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('integrations.gmail.client_id'),
                'client_secret' => config('integrations.gmail.client_secret'),
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => config('integrations.gmail.redirect_uri'),
            ])
            ->throw()
            ->json();

        $profile = $this->request(Arr::get($token, 'access_token'))
            ->get('https://gmail.googleapis.com/gmail/v1/users/me/profile')
            ->throw()
            ->json();

        $existing = $user->gmailAccount;

        return GmailAccount::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'email' => Arr::get($profile, 'emailAddress'),
                'google_user_id' => Arr::get($profile, 'emailAddress'),
                'access_token' => Arr::get($token, 'access_token'),
                'refresh_token' => Arr::get($token, 'refresh_token', $existing?->refresh_token),
                'token_expires_at' => now()->addSeconds((int) Arr::get($token, 'expires_in', 3600) - 60),
                'scopes' => Str::of((string) Arr::get($token, 'scope'))->explode(' ')->filter()->values()->all(),
                'history_id' => Arr::get($profile, 'historyId', $existing?->history_id),
                'connected_at' => now(),
            ]
        );
    }

    public function disconnect(GmailAccount $account): void
    {
        if ($account->access_token) {
            Http::asForm()->post('https://oauth2.googleapis.com/revoke', [
                'token' => $account->access_token,
            ]);
        }

        $account->delete();
    }

    public function accessToken(GmailAccount $account): string
    {
        if ($account->token_expires_at?->isFuture() && $account->access_token) {
            return $account->access_token;
        }

        $token = Http::asForm()
            ->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('integrations.gmail.client_id'),
                'client_secret' => config('integrations.gmail.client_secret'),
                'grant_type' => 'refresh_token',
                'refresh_token' => $account->refresh_token,
            ])
            ->throw()
            ->json();

        $account->forceFill([
            'access_token' => Arr::get($token, 'access_token'),
            'token_expires_at' => now()->addSeconds((int) Arr::get($token, 'expires_in', 3600) - 60),
            'scopes' => Str::of((string) Arr::get($token, 'scope', implode(' ', $account->scopes ?? [])))->explode(' ')->filter()->values()->all(),
        ])->save();

        return (string) $account->access_token;
    }

    private function request(string $accessToken): PendingRequest
    {
        return Http::withToken($accessToken)->acceptJson();
    }
}
