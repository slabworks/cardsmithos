<?php

namespace App\Jobs;

use App\Models\EmailMessage;
use App\Models\GmailAccount;
use App\Services\GmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendGmailMessage implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function __construct(
        private int $gmailAccountId,
        private string $to,
        private string $subject,
        private string $body,
        private ?string $threadId = null,
        private ?int $customerId = null,
    ) {
        $this->queue = 'gmail';
    }

    public function handle(): void
    {
        $account = GmailAccount::findOrFail($this->gmailAccountId);
        $gmail = new GmailService($account);

        try {
            $sent = $gmail->sendMessage($this->to, $this->subject, $this->body, $this->threadId);

            EmailMessage::create([
                'user_id' => $account->user_id,
                'gmail_account_id' => $account->id,
                'customer_id' => $this->customerId,
                'gmail_message_id' => $sent->getId(),
                'gmail_thread_id' => $sent->getThreadId(),
                'direction' => 'outbound',
                'from_address' => $account->email,
                'from_name' => null,
                'to_addresses' => [$this->to],
                'cc_addresses' => null,
                'subject' => $this->subject,
                'body_html' => $this->body,
                'body_text' => strip_tags($this->body),
                'snippet' => mb_substr(strip_tags($this->body), 0, 200),
                'labels' => ['SENT'],
                'is_read' => true,
                'received_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send Gmail message', [
                'to' => $this->to,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
