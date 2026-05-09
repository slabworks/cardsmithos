<?php

namespace App\Jobs;

use App\Models\GmailAccount;
use App\Services\Gmail\GmailSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncGmailAccount implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $gmailAccountId, public bool $full = false) {}

    public function handle(GmailSyncService $sync): void
    {
        $account = GmailAccount::query()->find($this->gmailAccountId);

        if ($account === null) {
            return;
        }

        $sync->sync($account, $this->full);
    }
}
