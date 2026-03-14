<?php

namespace App\Models;

use App\Enums\CardCondition;
use App\Enums\CardStatus;
use Database\Factories\CardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
                $rate = config('cardsmithos.hourly_rate', 100);
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
}
