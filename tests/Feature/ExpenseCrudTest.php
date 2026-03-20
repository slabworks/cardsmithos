<?php

use App\Enums\ExpenseCategory;
use App\Models\Expense;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('expense index requires authentication', function () {
    $response = $this->get(route('expenses.index'));

    $response->assertRedirect(route('login'));
});

test('expense index lists only own expenses', function () {
    $user = User::factory()->create();
    Expense::factory()->for($user)->count(3)->create();
    Expense::factory()->create(); // another user's expense

    $response = $this->actingAs($user)->get(route('expenses.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('expenses/index')
        ->has('expenses', 3));
});

test('expense create page renders', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('expenses.create'));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('expenses/create')
        ->has('categoryOptions', count(ExpenseCategory::cases())));
});

test('expense can be created', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('expenses.store'), [
        'description' => 'Card sleeves bulk order',
        'amount' => 49.99,
        'category' => 'supplies',
        'occurred_at' => '2026-03-15',
        'notes' => 'Ordered from supplier X',
    ]);

    $response->assertRedirect(route('expenses.index'));
    $this->assertDatabaseHas('expenses', [
        'user_id' => $user->id,
        'description' => 'Card sleeves bulk order',
        'amount' => 49.99,
        'category' => 'supplies',
        'notes' => 'Ordered from supplier X',
    ]);
});

test('expense creation validates required fields', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('expenses.store'), []);

    $response->assertInvalid(['description', 'amount', 'occurred_at']);
});

test('expense creation validates amount range', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('expenses.store'), [
        'description' => 'Test',
        'amount' => -5,
        'occurred_at' => '2026-03-15',
    ]);

    $response->assertInvalid(['amount']);
});

test('expense creation validates category enum', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('expenses.store'), [
        'description' => 'Test',
        'amount' => 10,
        'occurred_at' => '2026-03-15',
        'category' => 'invalid_category',
    ]);

    $response->assertInvalid(['category']);
});

test('expense show displays own expense', function () {
    $user = User::factory()->create();
    $expense = Expense::factory()->for($user)->create(['description' => 'Bubble mailers']);

    $response = $this->actingAs($user)->get(route('expenses.show', $expense));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('expenses/show')
        ->where('expense.description', 'Bubble mailers'));
});

test('expense show forbidden for other user', function () {
    $user = User::factory()->create();
    $expense = Expense::factory()->create(); // belongs to another user

    $response = $this->actingAs($user)->get(route('expenses.show', $expense));

    $response->assertForbidden();
});

test('expense edit page renders', function () {
    $user = User::factory()->create();
    $expense = Expense::factory()->for($user)->create();

    $response = $this->actingAs($user)->get(route('expenses.edit', $expense));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('expenses/edit')
        ->has('expense')
        ->has('categoryOptions'));
});

test('expense edit forbidden for other user', function () {
    $user = User::factory()->create();
    $expense = Expense::factory()->create();

    $response = $this->actingAs($user)->get(route('expenses.edit', $expense));

    $response->assertForbidden();
});

test('expense can be updated', function () {
    $user = User::factory()->create();
    $expense = Expense::factory()->for($user)->create();

    $response = $this->actingAs($user)->put(route('expenses.update', $expense), [
        'description' => 'Updated description',
        'amount' => 75.50,
        'category' => 'equipment',
        'occurred_at' => '2026-03-10',
    ]);

    $response->assertRedirect(route('expenses.show', $expense));
    $expense->refresh();
    expect($expense->description)->toBe('Updated description');
    expect($expense->amount)->toBe('75.50');
    expect($expense->category)->toBe(ExpenseCategory::Equipment);
});

test('expense update forbidden for other user', function () {
    $user = User::factory()->create();
    $expense = Expense::factory()->create();

    $response = $this->actingAs($user)->put(route('expenses.update', $expense), [
        'description' => 'Hacked',
        'amount' => 0.01,
        'occurred_at' => '2026-03-10',
    ]);

    $response->assertForbidden();
});

test('expense can be deleted', function () {
    $user = User::factory()->create();
    $expense = Expense::factory()->for($user)->create();

    $response = $this->actingAs($user)->delete(route('expenses.destroy', $expense));

    $response->assertRedirect(route('expenses.index'));
    $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
});

test('expense delete forbidden for other user', function () {
    $user = User::factory()->create();
    $expense = Expense::factory()->create();

    $response = $this->actingAs($user)->delete(route('expenses.destroy', $expense));

    $response->assertForbidden();
    $this->assertDatabaseHas('expenses', ['id' => $expense->id]);
});
