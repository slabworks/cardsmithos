<?php

namespace App\Models;

use App\Enums\CardCondition;
use App\Enums\CardStatus;
use Database\Factories\CardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Card extends Model
{
    /** @use HasFactory<CardFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'name',
        'work_done',
        'status',
        'condition_before',
        'condition_after',
        'restoration_hours',
        'estimated_fee',
        'photos',
        'timeline_share_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CardStatus::class,
            'condition_before' => CardCondition::class,
            'condition_after' => CardCondition::class,
            'restoration_hours' => 'decimal:2',
            'estimated_fee' => 'decimal:2',
            'photos' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Card $card): void {
            if ($card->restoration_hours !== null) {
                $rate = auth()->user()?->businessSettings?->hourly_rate ?? 0;
                $card->estimated_fee = (float) $card->restoration_hours * (float) $rate;
            } else {
                $card->estimated_fee = null;
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(CardActivity::class)->orderByDesc('occurred_at');
    }

    public function ensureTimelineShareToken(): string
    {
        if ($this->timeline_share_token === null || $this->timeline_share_token === '') {
            $this->timeline_share_token = Str::random(64);
            $this->save();
        }

        return $this->timeline_share_token;
    }

    public function rotateTimelineShareToken(): string
    {
        $this->timeline_share_token = Str::random(64);
        $this->save();

        return $this->timeline_share_token;
    }
}
