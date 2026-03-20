<?php

namespace App\Http\Controllers;

use App\Enums\ExpenseCategory;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Expense::class);

        $expenses = $request->user()
            ->expenses()
            ->latest('occurred_at')
            ->get();

        return Inertia::render('expenses/index', [
            'expenses' => $expenses,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Expense::class);

        return Inertia::render('expenses/create', [
            'categoryOptions' => array_map(
                fn (ExpenseCategory $case) => [
                    'value' => $case->value,
                    'label' => $case->label(),
                    'color' => $case->color(),
                ],
                ExpenseCategory::cases()
            ),
        ]);
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $request->user()->expenses()->create($request->validated());

        return to_route('expenses.index');
    }

    public function show(Expense $expense): Response
    {
        $this->authorize('view', $expense);

        return Inertia::render('expenses/show', [
            'expense' => $expense,
        ]);
    }

    public function edit(Expense $expense): Response
    {
        $this->authorize('update', $expense);

        return Inertia::render('expenses/edit', [
            'expense' => $expense,
            'categoryOptions' => array_map(
                fn (ExpenseCategory $case) => [
                    'value' => $case->value,
                    'label' => $case->label(),
                    'color' => $case->color(),
                ],
                ExpenseCategory::cases()
            ),
        ]);
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $expense->update($request->validated());

        return to_route('expenses.show', $expense);
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $this->authorize('delete', $expense);

        $expense->delete();

        return to_route('expenses.index');
    }
}
