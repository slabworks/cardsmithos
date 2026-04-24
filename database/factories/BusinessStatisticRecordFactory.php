<?php

namespace Database\Factories;

use App\Models\BusinessStatistic;
use App\Models\BusinessStatisticRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessStatisticRecord>
 */
class BusinessStatisticRecordFactory extends Factory
{
    public function definition(): array
    {
        return [
            'business_statistic_id' => BusinessStatistic::factory(),
            'recorded_at' => now(),
            'value' => fake()->randomFloat(2, 0, 1000),
            'source' => 'manual',
        ];
    }
}
