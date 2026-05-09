<?php

use App\Jobs\SyncGmailAccount;
use App\Models\GmailAccount;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('gmail:sync {user_id?} {--full}', function () {
    $query = GmailAccount::query();

    if ($this->argument('user_id')) {
        $query->where('user_id', $this->argument('user_id'));
    }

    $count = 0;
    $full = (bool) $this->option('full');

    $query->each(function (GmailAccount $account) use (&$count, $full): void {
        SyncGmailAccount::dispatch($account->id, $full);
        $count++;
    });

    $this->info("Queued {$count} Gmail sync job(s).");
})->purpose('Queue Gmail mailbox synchronization jobs');
