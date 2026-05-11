<?php

namespace App\Models;

use App\Enums\SubmissionStatus;
use Database\Factories\SubmissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Submission extends Model
{
    /** @use HasFactory<SubmissionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'customer_id',
        'status',
        'notes',
        'referral_source',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SubmissionStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return HasMany<Card>
     */
    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    /**
     * @return HasMany<Payment>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @return HasMany<Shipment>
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    /**
     * @return HasOne<ServiceWaiver>
     */
    public function serviceWaiver(): HasOne
    {
        return $this->hasOne(ServiceWaiver::class);
    }

    /**
     * Get the submission's service waiver, creating one if none exists.
     */
    public function getOrCreateServiceWaiver(): ServiceWaiver
    {
        $waiver = $this->serviceWaiver;

        if ($waiver === null) {
            $waiver = $this->serviceWaiver()->create([
                'expires_at' => now()->addDays(config('cardsmithos.waiver.expiration_days', 30)),
            ]);
        }

        return $waiver;
    }
}
