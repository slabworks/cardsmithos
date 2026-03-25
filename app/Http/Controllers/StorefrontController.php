<?php

namespace App\Http\Controllers;

use App\Models\BusinessSettings;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StorefrontController extends Controller
{
    public function index(Request $request): Response
    {
        $columns = ['id', 'company_name', 'store_slug', 'bio', 'country', 'location_name', 'hourly_rate', 'default_fixed_rate', 'currency'];

        $query = BusinessSettings::whereNotNull('store_slug')
            ->where('is_listed_in_directory', true)
            ->select($columns);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('bio', 'like', "%{$search}%")
                    ->orWhere('location_name', 'like', "%{$search}%");
            });
        }

        if ($countries = $request->query('countries')) {
            $codes = array_filter(explode(',', $countries), fn ($c) => strlen($c) === 2);
            if ($codes) {
                $query->whereIn('country', $codes);
            }
        }

        $sortField = match ($request->query('sort')) {
            'country' => 'country',
            'rate' => 'hourly_rate',
            default => 'company_name',
        };
        $sortDir = $request->query('dir') === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortField, $sortDir);

        // Separate query for all available countries (unfiltered)
        $availableCountries = BusinessSettings::whereNotNull('store_slug')
            ->where('is_listed_in_directory', true)
            ->whereNotNull('country')
            ->where('country', '!=', 'OT')
            ->distinct()
            ->pluck('country')
            ->sort()
            ->values();

        return Inertia::render('storefront/index', [
            'storefronts' => $query->paginate(12)->withQueryString(),
            'totalStorefronts' => BusinessSettings::whereNotNull('store_slug')->where('is_listed_in_directory', true)->count(),
            'availableCountries' => $availableCountries,
            'filters' => [
                'search' => $request->query('search', ''),
                'countries' => $request->query('countries', ''),
                'sort' => $request->query('sort', 'name'),
                'dir' => $request->query('dir', 'asc'),
            ],
        ]);
    }

    public function show(string $slug): Response
    {
        $settings = BusinessSettings::where('store_slug', $slug)->firstOrFail();

        return Inertia::render('storefront/show', [
            'slug' => $slug,
            'companyName' => $settings->company_name,
            'hourlyRate' => $settings->hourly_rate,
            'fixedRate' => $settings->default_fixed_rate,
            'currency' => $settings->currency,
            'bio' => $settings->bio,
            'instagramHandle' => $settings->instagram_handle,
            'tiktokHandle' => $settings->tiktok_handle,
            'country' => $settings->country,
            'locationName' => $settings->location_name,
            'messagingEnabled' => (bool) $settings->messaging_enabled,
        ]);
    }
}
