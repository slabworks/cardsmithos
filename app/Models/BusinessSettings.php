<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessSettings extends Model
{
    use HasFactory;

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
        'country',
        'location_name',
        'is_listed_in_directory',
        'hide_pricing',
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
            'is_listed_in_directory' => 'boolean',
            'hide_pricing' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
