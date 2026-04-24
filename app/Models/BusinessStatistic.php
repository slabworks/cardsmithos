<?php

namespace App\Models;

use Database\Factories\BusinessStatisticFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessStatistic extends Model
{
    /** @use HasFactory<BusinessStatisticFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'source',
        'system_key',
        'category',
        'group_name',
        'period',
        'value_type',
        'input_method',
        'description',
        'config',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'config' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<BusinessStatisticRecord>
     */
    public function records(): HasMany
    {
        return $this->hasMany(BusinessStatisticRecord::class);
    }

    /**
     * @return HasMany<BusinessStatisticRecord>
     */
    public function latestRecords(): HasMany
    {
        return $this->records()->latest('recorded_at');
    }
}
