<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GmailContact extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'gmail_account_id',
        'customer_id',
        'email',
        'name',
        'latest_subject',
        'latest_snippet',
        'latest_gmail_message_id',
        'latest_gmail_thread_id',
        'last_message_at',
        'last_synced_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latest_subject' => 'encrypted',
            'latest_snippet' => 'encrypted',
            'last_message_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(GmailAccount::class, 'gmail_account_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
