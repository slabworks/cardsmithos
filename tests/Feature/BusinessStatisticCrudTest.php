<?php

use App\Models\BusinessStatistic;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\User;

test('statistics index lists only owned statistics', function () {
    $user = User::factory()->create();
    BusinessStatistic::factory()->for($user)->count(2)->create();
    BusinessStatistic::factory()->create();

    $response = $this->actingAs($user)->get(route('statistics.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('statistics/index')
        ->has('statistics', 2));
});

test('user can create a custom statistic and slug is generated automatically', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('statistics.store'), [
        'name' => 'Collector Referrals',
        'category' => 'marketing',
        'group_name' => 'Lead Generation',
        'period' => 'monthly',
        'value_type' => 'number',
        'description' => 'Manual KPI',
        'sort_order' => 3,
    ]);

    $statistic = BusinessStatistic::query()->where('user_id', $user->id)->first();

    $response->assertRedirect(route('statistics.show', $statistic));
    expect($statistic)->not->toBeNull();
    expect($statistic->slug)->toBe('collector-referrals');
    expect($statistic->source)->toBe('custom');
    expect($statistic->input_method)->toBe('manual');
});

test('system statistics show calculated revenue this month', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();

    Payment::factory()->for($customer)->create([
        'amount' => 100.00,
        'paid_at' => now()->toDateString(),
    ]);
    Shipment::factory()->for($customer)->create([
        'amount' => 20.00,
        'shipped_at' => now()->toDateString(),
    ]);
    Expense::factory()->for($user)->create([
        'amount' => 10.00,
        'occurred_at' => now()->toDateString(),
    ]);

    $statistic = BusinessStatistic::factory()->for($user)->create([
        'name' => 'Revenue This Month',
        'slug' => 'revenue-this-month',
        'source' => 'system',
        'system_key' => 'revenue_this_month',
        'category' => 'finance',
        'group_name' => 'Financial Metrics',
        'period' => 'monthly',
        'value_type' => 'currency',
        'input_method' => 'derived',
    ]);

    $response = $this->actingAs($user)->get(route('statistics.show', $statistic));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('statistics/show')
        ->where('statistic.display_value', '$70.00')
        ->where('records.0.display_value', '$70.00'));
});

test('manual statistics can store records', function () {
    $user = User::factory()->create();
    $statistic = BusinessStatistic::factory()->for($user)->create();

    $response = $this->actingAs($user)->post(route('statistics.records.store', $statistic), [
        'value' => 42,
        'recorded_at' => now()->toDateString(),
        'source' => 'manual',
        'notes' => 'Strong week',
    ]);

    $response->assertRedirect(route('statistics.show', $statistic));
    $this->assertDatabaseHas('business_statistic_records', [
        'business_statistic_id' => $statistic->id,
        'notes' => 'Strong week',
    ]);
});

test('user can delete a statistic', function () {
    $user = User::factory()->create();
    $statistic = BusinessStatistic::factory()->for($user)->create();

    $response = $this->actingAs($user)->delete(route('statistics.destroy', $statistic));

    $response->assertRedirect(route('statistics.index'));
    $this->assertDatabaseMissing('business_statistics', [
        'id' => $statistic->id,
    ]);
});

test('user can delete a manual statistic record', function () {
    $user = User::factory()->create();
    $statistic = BusinessStatistic::factory()->for($user)->create();
    $record = $statistic->records()->create([
        'recorded_at' => now(),
        'value' => 55,
        'source' => 'manual',
        'notes' => 'Delete me',
    ]);

    $response = $this->actingAs($user)->delete(route('statistics.records.destroy', [
        'businessStatistic' => $statistic,
        'businessStatisticRecord' => $record,
    ]));

    $response->assertRedirect(route('statistics.show', $statistic));
    $this->assertDatabaseMissing('business_statistic_records', [
        'id' => $record->id,
    ]);
});
