<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessSettings extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'hourly_rate',
        'default_fixed_rate',
        'currency',
        'company_name',
        'tax_rate',
        'store_slug',
        'bio',
        'instagram_handle',
        'tiktok_handle',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hourly_rate' => 'decimal:2',
            'default_fixed_rate' => 'decimal:2',
            'tax_rate' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
