<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceWaiver extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'expires_at',
        'signed_at',
        'signer_name',
        'signer_email',
        'signer_ip',
        'signer_user_agent',
        'agreement_text',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'signed_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function isSigned(): bool
    {
        return $this->signed_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
