<?php

namespace App\Models;

use App\Enums\CardActivityType;
use Database\Factories\CardActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardActivity extends Model
{
    /** @use HasFactory<CardActivityFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'card_id',
        'type',
        'title',
        'description',
        'occurred_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CardActivityType::class,
            'occurred_at' => 'datetime',
        ];
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}
