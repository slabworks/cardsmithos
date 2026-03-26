import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowUpDown,
    ChevronLeft,
    ChevronRight,
    MapPin,
    Store,
} from 'lucide-react';
import { useCallback, useRef } from 'react';
import { COUNTRY_NAMES, countryToFlag } from '@/lib/countries';
import {
    index as storefrontIndex,
    show as storefrontShow,
} from '@/routes/storefront';

function formatCurrency(amount: string, currency: string): string {
    try {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency,
        }).format(Number(amount));
    } catch {
        return `${currency} ${Number(amount).toFixed(2)}`;
    }
}

type Storefront = {
    id: number;
    company_name: string | null;
    store_slug: string;
    bio: string | null;
    country: string | null;
    location_name: string | null;
    hourly_rate: string | null;
    default_fixed_rate: string | null;
    currency: string | null;
    hide_pricing: boolean;
};

type PaginatedData<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    next_page_url: string | null;
    prev_page_url: string | null;
};

type Filters = {
    search: string;
    countries: string;
    sort: string;
    dir: string;
};

function getLocationDisplay(s: Storefront): string | null {
    if (s.country && s.country !== 'OT') {
        return `${countryToFlag(s.country)} ${COUNTRY_NAMES[s.country] ?? s.country}`;
    }

    if (s.country === 'OT' && s.location_name) {
        return s.location_name;
    }

    return null;
}

export default function StorefrontIndex({
    storefronts,
    totalStorefronts,
    availableCountries,
    filters,
}: {
    storefronts: PaginatedData<Storefront>;
    totalStorefronts: number;
    availableCountries: string[];
    filters: Filters;
}) {
    const debounceRef = useRef<ReturnType<typeof setTimeout>>(null);

    const navigate = useCallback(
        (params: Partial<Filters>) => {
            const merged = { ...filters, ...params, page: '1' };

            // Strip empty values
            const query: Record<string, string> = {};

            for (const [k, v] of Object.entries(merged)) {
                if (v) {
                    query[k] = v;
                }
            }

            router.get(
                storefrontIndex.url({ query }),
                {},
                {
                    preserveState: true,
                    preserveScroll: true,
                },
            );
        },
        [filters],
    );

    const onSearch = (value: string) => {
        if (debounceRef.current) {
            clearTimeout(debounceRef.current);
        }

        debounceRef.current = setTimeout(() => {
            navigate({ search: value });
        }, 300);
    };

    const activeCountries = new Set(
        filters.countries ? filters.countries.split(',') : [],
    );

    const toggleCountry = (code: string) => {
        const next = new Set(activeCountries);

        if (next.has(code)) {
            next.delete(code);
        } else {
            next.add(code);
        }

        navigate({ countries: [...next].join(',') });
    };

    const toggleSort = (field: string) => {
        if (filters.sort === field) {
            navigate({ dir: filters.dir === 'asc' ? 'desc' : 'asc' });
        } else {
            navigate({ sort: field, dir: 'asc' });
        }
    };

    return (
        <>
            <Head title="Card Repair Directory — Find a Repair Shop">
                <meta
                    name="description"
                    content="Browse trading card repair shops. Find a card restoration specialist near you."
                />
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600"
                    rel="stylesheet"
                />
            </Head>
            <div className="min-h-screen bg-white text-[#1b1b18]">
                <header className="border-b border-[#e8e8e6] bg-white">
                    <div className="mx-auto flex max-w-5xl items-center justify-between gap-4 px-6 py-4 lg:px-8">
                        <Link
                            href="/"
                            className="text-lg font-semibold tracking-tight"
                        >
                            Cardsmith OS
                        </Link>
                        <span className="text-sm text-[#575754]">
                            Repair Shop Directory
                        </span>
                    </div>
                </header>

                <main className="mx-auto max-w-5xl px-6 py-10 lg:px-8 lg:py-14">
                    <div className="mb-8 text-center">
                        <h1 className="mb-2 text-3xl font-semibold tracking-tight lg:text-4xl">
                            Find a Card Repair Shop
                        </h1>
                        <p className="text-[#575754]">
                            Browse trading card restoration specialists
                        </p>
                    </div>

                    <div className="mb-6 space-y-3">
                        <div>
                            <input
                                type="text"
                                placeholder="Search shops by name, location, or description..."
                                defaultValue={filters.search}
                                onChange={(e) => onSearch(e.target.value)}
                                className="w-full rounded-lg border border-[#e8e8e6] bg-white px-4 py-2.5 text-sm transition-shadow outline-none focus:border-[#1b1b18] focus:ring-1 focus:ring-[#1b1b18]"
                            />
                        </div>

                        {availableCountries.length > 0 && (
                            <div className="flex flex-wrap gap-2">
                                {availableCountries.map((code) => (
                                    <button
                                        key={code}
                                        type="button"
                                        onClick={() => toggleCountry(code)}
                                        className={`inline-flex items-center gap-1 rounded-full border px-3 py-1 text-sm transition-colors ${
                                            activeCountries.has(code)
                                                ? 'border-[#1b1b18] bg-[#1b1b18] text-white'
                                                : 'border-[#e8e8e6] bg-white text-[#575754] hover:border-[#c8c8c5]'
                                        }`}
                                    >
                                        {countryToFlag(code)}{' '}
                                        {COUNTRY_NAMES[code] ?? code}
                                    </button>
                                ))}
                                {activeCountries.size > 0 && (
                                    <button
                                        type="button"
                                        onClick={() =>
                                            navigate({ countries: '' })
                                        }
                                        className="inline-flex items-center rounded-full border border-[#e8e8e6] px-3 py-1 text-sm text-[#575754] hover:border-[#c8c8c5]"
                                    >
                                        Clear filters
                                    </button>
                                )}
                            </div>
                        )}

                        <div className="flex gap-2">
                            {(
                                [
                                    ['name', 'Name'],
                                    ['country', 'Country'],
                                    ['rate', 'Rate'],
                                ] as const
                            ).map(([field, label]) => (
                                <button
                                    key={field}
                                    type="button"
                                    onClick={() => toggleSort(field)}
                                    className={`inline-flex items-center gap-1 rounded-md border px-2.5 py-1 text-xs font-medium transition-colors ${
                                        filters.sort === field
                                            ? 'border-[#1b1b18] bg-[#1b1b18] text-white'
                                            : 'border-[#e8e8e6] text-[#575754] hover:border-[#c8c8c5]'
                                    }`}
                                >
                                    <ArrowUpDown className="h-3 w-3" />
                                    {label}
                                    {filters.sort === field &&
                                        (filters.dir === 'asc'
                                            ? ' \u2191'
                                            : ' \u2193')}
                                </button>
                            ))}
                        </div>
                    </div>

                    {storefronts.data.length === 0 ? (
                        <div className="flex flex-col items-center justify-center gap-2 rounded-lg border border-[#e8e8e6] py-16 text-center">
                            <Store className="h-10 w-10 text-[#a1a19a]" />
                            <p className="text-sm text-[#575754]">
                                {totalStorefronts === 0
                                    ? 'No shops listed yet'
                                    : 'No shops match your filters'}
                            </p>
                        </div>
                    ) : (
                        <>
                            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                {storefronts.data.map((s) => {
                                    const location = getLocationDisplay(s);
                                    const curr = s.currency ?? 'USD';

                                    return (
                                        <Link
                                            key={s.id}
                                            href={storefrontShow.url(
                                                s.store_slug,
                                            )}
                                            className="group rounded-lg border border-[#e8e8e6] bg-white p-5 transition-shadow hover:shadow-md"
                                        >
                                            <h2 className="mb-1 font-semibold text-[#1b1b18] group-hover:underline">
                                                {s.company_name ??
                                                    'Card Repair Shop'}
                                            </h2>
                                            {location && (
                                                <p className="mb-2 flex items-center gap-1 text-xs text-[#575754]">
                                                    <MapPin className="h-3 w-3" />
                                                    {location}
                                                </p>
                                            )}
                                            {s.bio && (
                                                <p className="mb-3 line-clamp-2 text-sm text-[#575754]">
                                                    {s.bio}
                                                </p>
                                            )}
                                            {(s.hourly_rate !== null ||
                                                s.default_fixed_rate !==
                                                    null) && (
                                                <div className="flex flex-wrap gap-2 text-xs text-[#575754]">
                                                    {s.hide_pricing ? (
                                                        <span className="rounded-full border border-[#e8e8e6] px-2 py-0.5">
                                                            Contact for pricing
                                                        </span>
                                                    ) : (
                                                        <>
                                                            {s.hourly_rate !== null && (
                                                                <span className="rounded-full border border-[#e8e8e6] px-2 py-0.5">
                                                                    {formatCurrency(
                                                                        s.hourly_rate,
                                                                        curr,
                                                                    )}
                                                                    /hr
                                                                </span>
                                                            )}
                                                            {s.default_fixed_rate !==
                                                                null && (
                                                                <span className="rounded-full border border-[#e8e8e6] px-2 py-0.5">
                                                                    {formatCurrency(
                                                                        s.default_fixed_rate,
                                                                        curr,
                                                                    )}
                                                                    /card
                                                                </span>
                                                            )}
                                                        </>
                                                    )}
                                                </div>
                                            )}
                                        </Link>
                                    );
                                })}
                            </div>

                            {storefronts.last_page > 1 && (
                                <div className="mt-8 flex items-center justify-center gap-2">
                                    {storefronts.prev_page_url ? (
                                        <Link
                                            href={storefronts.prev_page_url}
                                            preserveState
                                            preserveScroll
                                            className="inline-flex items-center gap-1 rounded-md border border-[#e8e8e6] px-3 py-1.5 text-sm text-[#575754] hover:border-[#c8c8c5]"
                                        >
                                            <ChevronLeft className="h-4 w-4" />
                                            Previous
                                        </Link>
                                    ) : (
                                        <span className="inline-flex items-center gap-1 rounded-md border border-[#e8e8e6] px-3 py-1.5 text-sm text-[#c8c8c5]">
                                            <ChevronLeft className="h-4 w-4" />
                                            Previous
                                        </span>
                                    )}

                                    <span className="px-2 text-sm text-[#575754]">
                                        Page {storefronts.current_page} of{' '}
                                        {storefronts.last_page}
                                    </span>

                                    {storefronts.next_page_url ? (
                                        <Link
                                            href={storefronts.next_page_url}
                                            preserveState
                                            preserveScroll
                                            className="inline-flex items-center gap-1 rounded-md border border-[#e8e8e6] px-3 py-1.5 text-sm text-[#575754] hover:border-[#c8c8c5]"
                                        >
                                            Next
                                            <ChevronRight className="h-4 w-4" />
                                        </Link>
                                    ) : (
                                        <span className="inline-flex items-center gap-1 rounded-md border border-[#e8e8e6] px-3 py-1.5 text-sm text-[#c8c8c5]">
                                            Next
                                            <ChevronRight className="h-4 w-4" />
                                        </span>
                                    )}
                                </div>
                            )}
                        </>
                    )}
                </main>
            </div>
        </>
    );
}
