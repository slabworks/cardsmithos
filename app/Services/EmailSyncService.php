<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\EmailMessage;
use App\Models\GmailAccount;
use App\Models\Inquiry;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class EmailSyncService
{
    public function fullSync(GmailAccount $account, int $maxResults = 500): void
    {
        $gmail = new GmailService($account);
        $profile = $gmail->getProfile();
        $pageToken = null;
        $synced = 0;

        do {
            $result = $gmail->listMessages('', $pageToken, min(100, $maxResults - $synced));

            foreach ($result['messages'] as $messageMeta) {
                if ($synced >= $maxResults) {
                    break;
                }

                try {
                    $this->syncMessage($gmail, $account, $messageMeta['id']);
                } catch (\Exception $e) {
                    Log::warning("Skipping message {$messageMeta['id']}: {$e->getMessage()}");
                }
                $synced++;
            }

            $pageToken = $result['nextPageToken'];
        } while ($pageToken && $synced < $maxResults);

        $account->update([
            'history_id' => $profile['historyId'],
            'last_synced_at' => now(),
        ]);
    }

    public function incrementalSync(GmailAccount $account): void
    {
        $gmail = new GmailService($account);

        $result = $gmail->getHistory($account->history_id);

        if (! empty($result['fullSyncRequired'])) {
            $this->fullSync($account);

            return;
        }

        foreach ($result['messages'] as $messageMeta) {
            $this->syncMessage($gmail, $account, $messageMeta['id']);
        }

        $account->update([
            'history_id' => $result['historyId'],
            'last_synced_at' => now(),
        ]);
    }

    public function importAsInquiry(EmailMessage $message): Inquiry
    {
        return Inquiry::create([
            'user_id' => $message->user_id,
            'customer_id' => $message->customer_id,
            'inquiry_name' => $message->from_name ?? $message->from_address,
            'contact_detail' => $message->from_address,
            'communication_method' => 'email',
            'inquired_at' => $message->received_at,
            'converted' => false,
            'notes' => $message->subject,
            'gmail_message_id' => $message->gmail_message_id,
        ]);
    }

    public function associateWithCustomer(EmailMessage $message): void
    {
        $customer = Customer::where('user_id', $message->user_id)
            ->where(function ($query) use ($message) {
                $query->where('email', $message->from_address);

                foreach ($message->to_addresses ?? [] as $to) {
                    $query->orWhere('email', $to);
                }
            })
            ->first();

        if ($customer) {
            $message->update(['customer_id' => $customer->id]);

            // Also associate other messages in the same thread
            EmailMessage::where('user_id', $message->user_id)
                ->where('gmail_thread_id', $message->gmail_thread_id)
                ->whereNull('customer_id')
                ->update(['customer_id' => $customer->id]);
        }
    }

    private function syncMessage(GmailService $gmail, GmailAccount $account, string $messageId): void
    {
        // Skip if already synced
        if (EmailMessage::where('user_id', $account->user_id)->where('gmail_message_id', $messageId)->exists()) {
            return;
        }

        try {
            $data = $gmail->getMessage($messageId);
        } catch (\Exception) {
            return;
        }

        $fromParts = $this->parseEmailAddress($data['headers']['From'] ?? '');
        $toAddresses = $this->parseEmailList($data['headers']['To'] ?? '');
        $ccAddresses = $this->parseEmailList($data['headers']['Cc'] ?? '');

        $isOutbound = strtolower($fromParts['email']) === strtolower($account->email);

        $message = EmailMessage::create([
            'user_id' => $account->user_id,
            'gmail_account_id' => $account->id,
            'gmail_message_id' => $data['id'],
            'gmail_thread_id' => $data['threadId'],
            'direction' => $isOutbound ? 'outbound' : 'inbound',
            'from_address' => $fromParts['email'],
            'from_name' => $fromParts['name'],
            'to_addresses' => $toAddresses,
            'cc_addresses' => $ccAddresses ?: null,
            'subject' => $data['headers']['Subject'] ?? null,
            'body_text' => $data['body_text'],
            'body_html' => $data['body_html'],
            'snippet' => $data['snippet'],
            'labels' => $data['labelIds'],
            'is_read' => ! in_array('UNREAD', $data['labelIds']),
            'received_at' => Carbon::createFromTimestampMs($data['internalDate']),
        ]);

        // Store attachment metadata (cid: resolution happens at read time)
        foreach ($data['attachments'] as $attachment) {
            if ($attachment['id']) {
                $message->attachments()->create([
                    'gmail_attachment_id' => $attachment['id'],
                    'content_id' => $attachment['contentId'] ?? null,
                    'filename' => $attachment['filename'],
                    'mime_type' => $attachment['mimeType'],
                    'size' => $attachment['size'],
                ]);
            } elseif (($attachment['contentId'] ?? null) && ($attachment['inlineData'] ?? null)) {
                // Inline image embedded directly in MIME — store as base64 data URI
                $message->attachments()->create([
                    'gmail_attachment_id' => null,
                    'content_id' => $attachment['contentId'],
                    'filename' => $attachment['filename'],
                    'mime_type' => $attachment['mimeType'],
                    'size' => $attachment['size'],
                    'inline_data' => base64_encode($attachment['inlineData']),
                ]);
            }
        }

        // Auto-associate with customer
        $this->associateWithCustomer($message);
    }

    /**
     * @return array{name: string|null, email: string}
     */
    private function parseEmailAddress(string $header): array
    {
        if (preg_match('/^(.+?)\s*<(.+?)>$/', $header, $matches)) {
            return [
                'name' => trim($matches[1], '"\'  '),
                'email' => $matches[2],
            ];
        }

        return ['name' => null, 'email' => trim($header)];
    }

    /**
     * @return list<string>
     */
    private function parseEmailList(string $header): array
    {
        if (empty($header)) {
            return [];
        }

        $addresses = [];
        foreach (preg_split('/,\s*/', $header) as $part) {
            $parsed = $this->parseEmailAddress($part);
            $addresses[] = $parsed['email'];
        }

        return $addresses;
    }
}
