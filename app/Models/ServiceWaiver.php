<?php

namespace App\Models;

use Database\Factories\ServiceWaiverFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;

class ServiceWaiver extends Model
{
    /** @use HasFactory<ServiceWaiverFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function signedUrl(): ?string
    {
        if ($this->isSigned() || $this->isExpired()) {
            return null;
        }

        $relativeUrl = URL::temporarySignedRoute(
            'waiver.show',
            $this->expires_at,
            ['serviceWaiver' => $this],
            absolute: false
        );

        return url($relativeUrl);
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
