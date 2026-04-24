<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class BusinessStatisticSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->each(function (User $user): void {
            foreach ($this->defaults() as $index => $statistic) {
                $user->businessStatistics()->firstOrCreate(
                    ['slug' => $statistic['slug']],
                    $statistic + ['sort_order' => $index]
                );
            }
        });
    }

    private function defaults(): array
    {
        return [
            ['name' => 'Inquiries This Week', 'slug' => 'inquiries-this-week', 'source' => 'custom', 'category' => 'leads', 'group_name' => 'Lead Generation', 'period' => 'weekly', 'value_type' => 'number', 'input_method' => 'manual'],
            ['name' => 'Inquiries This Month', 'slug' => 'inquiries-this-month', 'source' => 'custom', 'category' => 'leads', 'group_name' => 'Lead Generation', 'period' => 'monthly', 'value_type' => 'number', 'input_method' => 'manual'],
            ['name' => 'Conversion Rate', 'slug' => 'conversion-rate', 'source' => 'custom', 'category' => 'sales', 'group_name' => 'Lead Generation', 'period' => 'monthly', 'value_type' => 'percentage', 'input_method' => 'manual'],
            ['name' => 'Leads from Instagram', 'slug' => 'leads-from-instagram', 'source' => 'custom', 'category' => 'marketing', 'group_name' => 'Lead Generation', 'period' => 'monthly', 'value_type' => 'number', 'input_method' => 'manual'],
            ['name' => 'Leads from Email', 'slug' => 'leads-from-email', 'source' => 'custom', 'category' => 'marketing', 'group_name' => 'Lead Generation', 'period' => 'monthly', 'value_type' => 'number', 'input_method' => 'manual'],
            ['name' => 'Repeat Customers', 'slug' => 'repeat-customers', 'source' => 'system', 'system_key' => 'repeat_customers', 'category' => 'sales', 'group_name' => 'Sales Performance', 'period' => 'monthly', 'value_type' => 'number', 'input_method' => 'derived'],
            ['name' => 'Average Order Value', 'slug' => 'average-order-value', 'source' => 'system', 'system_key' => 'average_order_value', 'category' => 'finance', 'group_name' => 'Financial Metrics', 'period' => 'monthly', 'value_type' => 'currency', 'input_method' => 'derived'],
            ['name' => 'Revenue This Month', 'slug' => 'revenue-this-month', 'source' => 'system', 'system_key' => 'revenue_this_month', 'category' => 'finance', 'group_name' => 'Financial Metrics', 'period' => 'monthly', 'value_type' => 'currency', 'input_method' => 'derived'],
        ];
    }
}
