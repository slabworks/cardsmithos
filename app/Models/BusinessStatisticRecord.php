<?php

namespace App\Models;

use Database\Factories\BusinessStatisticRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessStatisticRecord extends Model
{
    /** @use HasFactory<BusinessStatisticRecordFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'business_statistic_id',
        'period_start',
        'period_end',
        'recorded_at',
        'value',
        'source',
        'notes',
        'meta',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'recorded_at' => 'datetime',
            'value' => 'decimal:2',
            'meta' => 'array',
        ];
    }

    public function businessStatistic(): BelongsTo
    {
        return $this->belongsTo(BusinessStatistic::class);
    }
}
