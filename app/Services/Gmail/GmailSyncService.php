<?php

namespace App\Services\Gmail;

use App\Models\Customer;
use App\Models\GmailAccount;
use App\Models\GmailContact;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Throwable;

class GmailSyncService
{
    public function __construct(private GmailClient $client) {}

    public function sync(GmailAccount $account, bool $full = false): void
    {
        if (! $full && $account->last_synced_at !== null && $account->history_id) {
            try {
                $this->partialSync($account);

                return;
            } catch (Throwable) {
                $this->fullSync($account);

                return;
            }
        }

        $this->fullSync($account);
    }

    public function fullSync(GmailAccount $account): void
    {
        $pageToken = null;
        $synced = 0;
        $limit = (int) config('integrations.gmail.contact_sync_messages', 1000);

        do {
            $response = $this->client->listMessages($account, $pageToken);
            $messages = Arr::get($response, 'messages', []);

            foreach ($messages as $message) {
                if ($synced >= $limit) {
                    break 2;
                }

                $this->importMessageMetadata($account, (string) Arr::get($message, 'id'));
                $synced++;
            }

            $pageToken = Arr::get($response, 'nextPageToken');
        } while ($pageToken !== null);

        $account->forceFill(['last_synced_at' => now()])->save();
    }

    public function partialSync(GmailAccount $account): void
    {
        $pageToken = null;
        $messageIds = [];
        $latestHistoryId = $account->history_id;

        do {
            $response = $this->client->listHistory($account, (string) $account->history_id, $pageToken);
            $latestHistoryId = Arr::get($response, 'historyId', $latestHistoryId);

            foreach (Arr::get($response, 'history', []) as $history) {
                foreach (Arr::get($history, 'messagesAdded', []) as $added) {
                    $message = Arr::get($added, 'message', []);
                    $messageId = Arr::get($message, 'id');
                    $labels = Arr::get($message, 'labelIds', []);

                    if ($messageId && in_array('INBOX', $labels, true)) {
                        $messageIds[$messageId] = $messageId;
                    }
                }
            }

            $pageToken = Arr::get($response, 'nextPageToken');
        } while ($pageToken !== null);

        foreach ($messageIds as $messageId) {
            $this->importMessageMetadata($account, $messageId);
        }

        $account->forceFill([
            'history_id' => $latestHistoryId,
            'last_synced_at' => now(),
        ])->save();
    }

    public function importMessageMetadata(GmailAccount $account, string $messageId): ?GmailContact
    {
        $message = $this->client->getMessageMetadata($account, $messageId);
        $parsedMessage = $this->parseMetadataMessage($message);
        $fromEmail = $parsedMessage['from']['email'] ?? null;

        if ($fromEmail === null || mb_strtolower($fromEmail) === mb_strtolower((string) $account->email)) {
            return null;
        }

        $customer = $this->matchCustomer($account, $fromEmail);
        $existing = GmailContact::query()
            ->where('gmail_account_id', $account->id)
            ->where('email', $fromEmail)
            ->first();

        if ($existing?->last_message_at && $parsedMessage['sent_at'] && $existing->last_message_at->greaterThan($parsedMessage['sent_at'])) {
            return $existing;
        }

        $contact = GmailContact::query()->updateOrCreate(
            [
                'gmail_account_id' => $account->id,
                'email' => $fromEmail,
            ],
            [
                'customer_id' => $customer?->id ?? $existing?->customer_id,
                'name' => $parsedMessage['from']['name'] ?? $existing?->name,
                'latest_subject' => $parsedMessage['subject'],
                'latest_snippet' => $this->preview($parsedMessage['snippet']),
                'latest_gmail_message_id' => $parsedMessage['id'],
                'latest_gmail_thread_id' => $parsedMessage['thread_id'],
                'last_message_at' => $parsedMessage['sent_at'],
                'last_synced_at' => now(),
            ]
        );

        if ($parsedMessage['history_id']) {
            $account->forceFill(['history_id' => $parsedMessage['history_id']])->save();
        }

        return $contact;
    }

    /**
     * @param  array<string, mixed>  $message
     * @return array<string, mixed>
     */
    private function parseMetadataMessage(array $message): array
    {
        $payload = Arr::get($message, 'payload', []);
        $headers = $this->headers(Arr::get($payload, 'headers', []));

        return [
            'id' => (string) Arr::get($message, 'id'),
            'thread_id' => (string) Arr::get($message, 'threadId'),
            'history_id' => Arr::get($message, 'historyId'),
            'from' => $this->addresses($headers['from'] ?? '')[0] ?? ['name' => null, 'email' => null],
            'subject' => $this->decodeEntities($headers['subject'] ?? '') ?: null,
            'snippet' => $this->decodeEntities((string) Arr::get($message, 'snippet', '')) ?: null,
            'sent_at' => $this->sentAt($message, $headers),
        ];
    }

    /**
     * @param  list<array{name?: string, value?: string}>  $headers
     * @return array<string, string>
     */
    private function headers(array $headers): array
    {
        return collect($headers)
            ->mapWithKeys(fn (array $header): array => [mb_strtolower((string) $header['name']) => (string) $header['value']])
            ->all();
    }

    /**
     * @return list<array{name: string|null, email: string}>
     */
    private function addresses(string $header): array
    {
        preg_match_all('/(?:(?:"?([^"<,]*)"?)\s*)?<?([A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,})>?/i', $header, $matches, PREG_SET_ORDER);

        return collect($matches)
            ->map(fn (array $match): array => [
                'name' => trim((string) ($match[1] ?? '')) ?: null,
                'email' => mb_strtolower((string) $match[2]),
            ])
            ->unique('email')
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $message
     * @param  array<string, string>  $headers
     */
    private function sentAt(array $message, array $headers): ?Carbon
    {
        $internalDate = Arr::get($message, 'internalDate');

        if ($internalDate) {
            return Carbon::createFromTimestamp((int) floor(((int) $internalDate) / 1000));
        }

        if (isset($headers['date'])) {
            return Carbon::parse($headers['date']);
        }

        return null;
    }

    private function matchCustomer(GmailAccount $account, string $email): ?Customer
    {
        return Customer::query()
            ->where('user_id', $account->user_id)
            ->where('email', mb_strtolower($email))
            ->first();
    }

    private function preview(?string $snippet): ?string
    {
        if ($snippet === null) {
            return null;
        }

        return Str::of($snippet)
            ->squish()
            ->limit(100, '')
            ->toString();
    }

    private function decodeEntities(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
