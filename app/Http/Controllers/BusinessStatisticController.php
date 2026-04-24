<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBusinessStatisticRequest;
use App\Http\Requests\UpdateBusinessStatisticRequest;
use App\Models\BusinessStatistic;
use App\Services\BusinessStatisticValueResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BusinessStatisticController extends Controller
{
    public function __construct(private BusinessStatisticValueResolver $valueResolver) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', BusinessStatistic::class);

        return Inertia::render('statistics/index', [
            'statistics' => $this->valueResolver->summariesFor($request->user()),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', BusinessStatistic::class);

        return Inertia::render('statistics/create', [
            'options' => $this->formOptions(),
        ]);
    }

    public function store(StoreBusinessStatisticRequest $request): RedirectResponse
    {
        $this->authorize('create', BusinessStatistic::class);

        $validated = $request->validated();
        $validated['slug'] = $this->uniqueSlug($request->user(), $validated['name']);
        $validated['source'] = 'custom';
        $validated['input_method'] = 'manual';

        $statistic = $request->user()->businessStatistics()->create($validated);

        return to_route('statistics.show', $statistic);
    }

    public function show(BusinessStatistic $businessStatistic): Response
    {
        $this->authorize('view', $businessStatistic);

        $businessStatistic->load(['records' => fn ($query) => $query->latest('recorded_at')]);

        return Inertia::render('statistics/show', [
            'statistic' => $this->valueResolver->summary($businessStatistic),
            'records' => $this->valueResolver->history($businessStatistic),
        ]);
    }

    public function edit(BusinessStatistic $businessStatistic): Response
    {
        $this->authorize('update', $businessStatistic);

        return Inertia::render('statistics/edit', [
            'statistic' => $businessStatistic,
            'options' => $this->formOptions(),
        ]);
    }

    public function update(UpdateBusinessStatisticRequest $request, BusinessStatistic $businessStatistic): RedirectResponse
    {
        $validated = $request->validated();

        if ($businessStatistic->input_method === 'manual') {
            $validated['slug'] = $this->uniqueSlug($request->user(), $validated['name'], $businessStatistic->id);
        }

        $businessStatistic->update($validated);

        return to_route('statistics.show', $businessStatistic);
    }

    public function destroy(BusinessStatistic $businessStatistic): RedirectResponse
    {
        $this->authorize('delete', $businessStatistic);

        $businessStatistic->delete();

        return to_route('statistics.index');
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function formOptions(): array
    {
        return [
            'categories' => ['sales', 'leads', 'marketing', 'finance', 'ops'],
            'periods' => ['daily', 'weekly', 'monthly', 'yearly', 'custom'],
            'valueTypes' => ['number', 'currency', 'percentage'],
        ];
    }

    private function uniqueSlug($user, string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $suffix = 2;

        while ($user->businessStatistics()
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
