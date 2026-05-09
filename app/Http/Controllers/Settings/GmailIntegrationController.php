<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Jobs\SyncGmailAccount;
use App\Services\Gmail\GmailOAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class GmailIntegrationController extends Controller
{
    public function edit(Request $request): Response
    {
        $account = $request->user()->gmailAccount;
        $missingConfig = $this->missingConfig();

        return Inertia::render('settings/integrations', [
            'gmail' => [
                'configured' => $missingConfig === [],
                'missingConfig' => $missingConfig,
                'connected' => $account !== null,
                'email' => $account?->email,
                'lastSyncedAt' => $account?->last_synced_at,
                'scopes' => $account?->scopes ?? [],
            ],
        ]);
    }

    public function redirect(Request $request, GmailOAuthService $oauth): RedirectResponse
    {
        abort_unless($this->missingConfig() === [], 409, 'Gmail integration is not configured.');

        $state = Str::random(40);
        $request->session()->put('gmail_oauth_state', $state);

        return redirect()->away($oauth->authorizationUrl($request->user(), $state));
    }

    public function callback(Request $request, GmailOAuthService $oauth): RedirectResponse
    {
        $expectedState = (string) $request->session()->pull('gmail_oauth_state');
        $actualState = (string) $request->query('state');

        abort_if($expectedState === '' || ! hash_equals($expectedState, $actualState), 403);

        if ($request->query('error')) {
            return to_route('integrations.edit');
        }

        $request->validate(['code' => ['required', 'string']]);

        $account = $oauth->connect($request->user(), (string) $request->query('code'));

        SyncGmailAccount::dispatch($account->id, true);

        return to_route('integrations.edit');
    }

    public function destroy(Request $request, GmailOAuthService $oauth): RedirectResponse
    {
        $account = $request->user()->gmailAccount;

        if ($account !== null) {
            $oauth->disconnect($account);
        }

        return to_route('integrations.edit');
    }

    /**
     * @return list<string>
     */
    private function missingConfig(): array
    {
        return collect([
            'GMAIL_CLIENT_ID' => config('integrations.gmail.client_id'),
            'GMAIL_CLIENT_SECRET' => config('integrations.gmail.client_secret'),
            'GMAIL_REDIRECT_URI' => config('integrations.gmail.redirect_uri'),
        ])
            ->filter(fn ($value): bool => blank($value))
            ->keys()
            ->values()
            ->all();
    }
}
