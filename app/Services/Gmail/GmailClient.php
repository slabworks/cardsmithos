<?php

namespace App\Services\Gmail;

use App\Models\GmailAccount;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class GmailClient
{
    public function __construct(private GmailOAuthService $oauth) {}

    /**
     * @return array<string, mixed>
     */
    public function listMessages(GmailAccount $account, ?string $pageToken = null): array
    {
        return $this->request($account)
            ->get('/users/me/messages', array_filter([
                'q' => config('integrations.gmail.contact_sync_query'),
                'maxResults' => config('integrations.gmail.sync_page_size', 50),
                'pageToken' => $pageToken,
            ]))
            ->throw()
            ->json();
    }

    /**
     * @return array<string, mixed>
     */
    public function getMessageMetadata(GmailAccount $account, string $messageId): array
    {
        return $this->request($account)
            ->get('/users/me/messages/'.$messageId, [
                'format' => 'metadata',
            ])
            ->throw()
            ->json();
    }

    /**
     * @return array<string, mixed>
     */
    public function listHistory(GmailAccount $account, string $historyId, ?string $pageToken = null): array
    {
        return $this->request($account)
            ->get('/users/me/history', array_filter([
                'startHistoryId' => $historyId,
                'historyTypes' => 'messageAdded',
                'pageToken' => $pageToken,
            ]))
            ->throw()
            ->json();
    }

    private function request(GmailAccount $account): PendingRequest
    {
        return Http::baseUrl('https://gmail.googleapis.com/gmail/v1')
            ->withToken($this->oauth->accessToken($account))
            ->acceptJson();
    }
}
