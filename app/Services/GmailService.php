<?php

namespace App\Services;

use App\Models\GmailAccount;
use Google\Client as GoogleClient;
use Google\Service\Exception;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;

class GmailService
{
    private GoogleClient $client;

    private Gmail $gmail;

    public function __construct(private GmailAccount $account)
    {
        $this->client = new GoogleClient;
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setAccessToken($account->access_token);

        $this->refreshTokenIfNeeded();

        $this->gmail = new Gmail($this->client);
    }

    public function refreshTokenIfNeeded(): void
    {
        if (! $this->account->isTokenExpired()) {
            return;
        }

        $this->client->fetchAccessTokenWithRefreshToken($this->account->refresh_token);
        $token = $this->client->getAccessToken();

        $this->account->update([
            'access_token' => $token['access_token'],
            'token_expires_at' => now()->addSeconds($token['expires_in']),
        ]);
    }

    /**
     * @return array{messages: array<array{id: string, threadId: string}>, nextPageToken: string|null}
     */
    public function listMessages(string $query = '', ?string $pageToken = null, int $maxResults = 100): array
    {
        $params = ['maxResults' => $maxResults];

        if ($query) {
            $params['q'] = $query;
        }

        if ($pageToken) {
            $params['pageToken'] = $pageToken;
        }

        $response = $this->gmail->users_messages->listUsersMessages('me', $params);

        return [
            'messages' => array_map(fn (Message $msg) => [
                'id' => $msg->getId(),
                'threadId' => $msg->getThreadId(),
            ], $response->getMessages() ?? []),
            'nextPageToken' => $response->getNextPageToken(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getMessage(string $messageId): array
    {
        $message = $this->gmail->users_messages->get('me', $messageId, ['format' => 'full']);

        $headers = $this->parseHeaders($message->getPayload()->getHeaders());
        $body = $this->extractBody($message->getPayload());
        $attachments = $this->extractAttachmentMeta($message->getPayload(), $messageId);

        return [
            'id' => $message->getId(),
            'threadId' => $message->getThreadId(),
            'labelIds' => $message->getLabelIds() ?? [],
            'snippet' => $message->getSnippet(),
            'internalDate' => $message->getInternalDate(),
            'headers' => $headers,
            'body_text' => $body['text'],
            'body_html' => $body['html'],
            'attachments' => $attachments,
        ];
    }

    public function getSignature(): string
    {
        try {
            $sendAs = $this->gmail->users_settings_sendAs->get('me', $this->account->email);

            return $sendAs->getSignature() ?? '';
        } catch (Exception) {
            return '';
        }
    }

    public function sendMessage(string $to, string $subject, string $body, ?string $threadId = null): Message
    {
        $from = $this->account->email;

        $signature = $this->getSignature();
        if ($signature) {
            $body .= '<div class="gmail_signature"><br>--<br>'.$signature.'</div>';
        }

        $to = str_replace(["\r", "\n"], '', $to);
        $subject = str_replace(["\r", "\n"], '', $subject);

        $raw = "From: {$from}\r\n";
        $raw .= "To: {$to}\r\n";
        $raw .= "Subject: {$subject}\r\n";
        $raw .= "MIME-Version: 1.0\r\n";
        $raw .= "Content-Type: text/html; charset=utf-8\r\n\r\n";
        $raw .= $body;

        $message = new Message;
        $message->setRaw(rtrim(strtr(base64_encode($raw), '+/', '-_'), '='));

        if ($threadId) {
            $message->setThreadId($threadId);
        }

        return $this->gmail->users_messages->send('me', $message);
    }

    public function getAttachment(string $messageId, string $attachmentId): string
    {
        $attachment = $this->gmail->users_messages_attachments->get('me', $messageId, $attachmentId);

        return base64_decode(strtr($attachment->getData(), '-_', '+/'));
    }

    /**
     * @return array{messages: array<array{id: string}>, historyId: string}
     */
    public function getHistory(string $startHistoryId): array
    {
        $messages = [];
        $pageToken = null;
        $latestHistoryId = $startHistoryId;

        do {
            $params = [
                'startHistoryId' => $startHistoryId,
                'historyTypes' => ['messageAdded'],
            ];

            if ($pageToken) {
                $params['pageToken'] = $pageToken;
            }

            try {
                $response = $this->gmail->users_history->listUsersHistory('me', $params);
            } catch (Exception $e) {
                if ($e->getCode() === 404) {
                    // History ID too old, need full sync
                    return ['messages' => [], 'historyId' => $startHistoryId, 'fullSyncRequired' => true];
                }
                throw $e;
            }

            $latestHistoryId = $response->getHistoryId();

            foreach ($response->getHistory() ?? [] as $history) {
                foreach ($history->getMessagesAdded() ?? [] as $added) {
                    $messages[] = ['id' => $added->getMessage()->getId()];
                }
            }

            $pageToken = $response->getNextPageToken();
        } while ($pageToken);

        return [
            'messages' => $messages,
            'historyId' => $latestHistoryId,
        ];
    }

    public function getProfile(): array
    {
        $profile = $this->gmail->users->getProfile('me');

        return [
            'emailAddress' => $profile->getEmailAddress(),
            'historyId' => $profile->getHistoryId(),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function parseHeaders(array $headers): array
    {
        $parsed = [];
        $relevant = ['From', 'To', 'Cc', 'Subject', 'Date', 'Message-ID', 'In-Reply-To'];

        foreach ($headers as $header) {
            if (in_array($header->getName(), $relevant)) {
                $parsed[$header->getName()] = $header->getValue();
            }
        }

        return $parsed;
    }

    /**
     * @return array{text: string|null, html: string|null}
     */
    private function extractBody($payload): array
    {
        $text = null;
        $html = null;

        if ($payload->getMimeType() === 'text/plain' && $payload->getBody()->getData()) {
            $text = base64_decode(strtr($payload->getBody()->getData(), '-_', '+/'));
        }

        if ($payload->getMimeType() === 'text/html' && $payload->getBody()->getData()) {
            $html = base64_decode(strtr($payload->getBody()->getData(), '-_', '+/'));
        }

        foreach ($payload->getParts() ?? [] as $part) {
            $partBody = $this->extractBody($part);
            $text = $text ?? $partBody['text'];
            $html = $html ?? $partBody['html'];
        }

        return ['text' => $text, 'html' => $html];
    }

    /**
     * @return array<array{id: string, filename: string, mimeType: string, size: int}>
     */
    private function extractAttachmentMeta($payload, string $messageId): array
    {
        $attachments = [];

        foreach ($payload->getParts() ?? [] as $part) {
            $contentId = null;
            foreach ($part->getHeaders() ?? [] as $header) {
                if (strtolower($header->getName()) === 'content-id') {
                    $contentId = trim($header->getValue(), '<>');
                    break;
                }
            }

            if ($part->getBody()->getAttachmentId()) {
                $attachments[] = [
                    'id' => $part->getBody()->getAttachmentId(),
                    'contentId' => $contentId,
                    'filename' => $part->getFilename() ?: 'inline',
                    'mimeType' => $part->getMimeType(),
                    'size' => $part->getBody()->getSize(),
                    'inlineData' => null,
                ];
            } elseif ($contentId && $part->getBody()->getData() && str_starts_with($part->getMimeType(), 'image/')) {
                // Inline image embedded directly in MIME (no attachment ID)
                $attachments[] = [
                    'id' => null,
                    'contentId' => $contentId,
                    'filename' => $part->getFilename() ?: 'inline',
                    'mimeType' => $part->getMimeType(),
                    'size' => $part->getBody()->getSize(),
                    'inlineData' => base64_decode(strtr($part->getBody()->getData(), '-_', '+/')),
                ];
            }

            // Recurse into nested parts
            $attachments = array_merge($attachments, $this->extractAttachmentMeta($part, $messageId));
        }

        return $attachments;
    }
}
