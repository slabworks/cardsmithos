<?php

namespace App\Jobs;

use App\Models\GmailAccount;
use App\Services\EmailSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncGmailMessages implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [30, 60, 120];

    public function __construct(
        private ?int $gmailAccountId = null
    ) {
        $this->queue = 'gmail';
    }

    public function handle(EmailSyncService $syncService): void
    {
        $accounts = $this->gmailAccountId
            ? GmailAccount::where('id', $this->gmailAccountId)->get()
            : GmailAccount::whereIn('user_id', function ($query) {
                $query->select('user_id')
                    ->from('sessions')
                    ->where('last_activity', '>=', now()->subDay()->timestamp);
            })->get();

        foreach ($accounts as $account) {
            try {
                if ($account->history_id) {
                    $syncService->incrementalSync($account);
                } else {
                    $syncService->fullSync($account);
                }
            } catch (\Exception $e) {
                Log::error('Gmail sync failed for account '.$account->email, [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
