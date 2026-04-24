<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'status',
        'email',
        'phone',
        'address',
        'notes',
        'referral_source',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CustomerStatus::class,
            'waiver_agreed' => 'boolean',
            'waiver_agreed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
     * Get the customer's service waiver, creating one if none exists (e.g. for customers created before waivers were added).
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
