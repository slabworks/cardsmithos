import { Head, Link } from '@inertiajs/react';
import { MapPin } from 'lucide-react';
import { COUNTRY_NAMES, countryToFlag } from '@/lib/countries';

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

function InstagramIcon({ className }: { className?: string }) {
    return (
        <svg
            className={className}
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
        >
            <rect width="20" height="20" x="2" y="2" rx="5" ry="5" />
            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
            <line x1="17.5" x2="17.51" y1="6.5" y2="6.5" />
        </svg>
    );
}

function TiktokIcon({ className }: { className?: string }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="currentColor">
            <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1v-3.5a6.37 6.37 0 0 0-.79-.05A6.34 6.34 0 0 0 3.15 15a6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.34-6.34V9.05a8.27 8.27 0 0 0 4.76 1.5V7.12a4.83 4.83 0 0 1-1-.43z" />
        </svg>
    );
}

export default function StorefrontShow({
    companyName,
    hourlyRate,
    fixedRate,
    currency,
    bio,
    instagramHandle,
    tiktokHandle,
    country,
    locationName,
}: {
    companyName: string | null;
    hourlyRate: string | null;
    fixedRate: string | null;
    currency: string | null;
    bio: string | null;
    instagramHandle: string | null;
    tiktokHandle: string | null;
    country: string | null;
    locationName: string | null;
}) {
    const curr = currency ?? 'USD';
    const hasRates = hourlyRate !== null || fixedRate !== null;
    const hasSocials = instagramHandle || tiktokHandle;
    const locationDisplay =
        country && country !== 'OT'
            ? `${countryToFlag(country)} ${COUNTRY_NAMES[country] ?? country}`
            : country === 'OT' && locationName
              ? locationName
              : null;

    return (
        <>
            <Head
                title={`${companyName ?? 'Storefront'} — Card Repair Services`}
            >
                <meta
                    name="description"
                    content={`${companyName ?? 'Card repair shop'} — trading card repair services${hourlyRate ? `, starting at ${formatCurrency(hourlyRate, curr)}/hr` : ''}.`}
                />
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600"
                    rel="stylesheet"
                />
            </Head>
            <div className="min-h-screen bg-white text-[#1b1b18]">
                <header className="border-b border-[#e8e8e6] bg-white">
                    <div className="mx-auto flex max-w-4xl items-center justify-between gap-4 px-6 py-4 lg:px-8">
                        <span className="text-lg font-semibold tracking-tight">
                            {companyName ?? 'Card Repair Shop'}
                        </span>
                        <Link
                            href="/"
                            className="text-sm text-[#575754] hover:text-[#1b1b18]"
                        >
                            Powered by Cardsmith OS
                        </Link>
                    </div>
                </header>

                <main className="mx-auto max-w-4xl px-6 py-12 lg:px-8 lg:py-16">
                    <section className="mb-12 text-center">
                        <h1 className="mb-4 text-3xl font-semibold tracking-tight text-[#1b1b18] lg:text-4xl">
                            {companyName ?? 'Card Repair Shop'}
                        </h1>
                        {locationDisplay && (
                            <p className="mt-2 flex items-center justify-center gap-1.5 text-sm text-[#575754]">
                                <MapPin className="h-4 w-4" />
                                {locationDisplay}
                            </p>
                        )}
                        {bio && (
                            <p className="mx-auto max-w-2xl text-lg leading-relaxed whitespace-pre-line text-[#575754]">
                                {bio}
                            </p>
                        )}
                        {hasSocials && (
                            <div className="mt-4 flex items-center justify-center gap-4">
                                {instagramHandle && (
                                    <a
                                        href={`https://instagram.com/${instagramHandle}`}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="inline-flex items-center gap-1.5 text-sm text-[#575754] hover:text-[#1b1b18]"
                                    >
                                        <InstagramIcon className="h-5 w-5" />@
                                        {instagramHandle}
                                    </a>
                                )}
                                {tiktokHandle && (
                                    <a
                                        href={`https://tiktok.com/@${tiktokHandle}`}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="inline-flex items-center gap-1.5 text-sm text-[#575754] hover:text-[#1b1b18]"
                                    >
                                        <TiktokIcon className="h-5 w-5" />@
                                        {tiktokHandle}
                                    </a>
                                )}
                            </div>
                        )}
                    </section>

                    {hasRates && (
                        <section className="mb-12">
                            <h2 className="mb-6 text-center text-xl font-medium text-[#1b1b18]">
                                Pricing
                            </h2>
                            <div
                                className={`grid gap-6 ${hourlyRate !== null && fixedRate !== null ? 'sm:grid-cols-2' : 'mx-auto max-w-sm'}`}
                            >
                                {hourlyRate !== null && (
                                    <div className="rounded-lg border border-[#e8e8e6] bg-[#fafaf9] p-6 text-center">
                                        <p className="mb-1 text-sm font-medium text-[#575754]">
                                            Hourly rate
                                        </p>
                                        <p className="text-3xl font-semibold text-[#1b1b18]">
                                            {formatCurrency(hourlyRate, curr)}
                                        </p>
                                        <p className="mt-1 text-sm text-[#575754]">
                                            per hour
                                        </p>
                                    </div>
                                )}
                                {fixedRate !== null && (
                                    <div className="rounded-lg border border-[#e8e8e6] bg-[#fafaf9] p-6 text-center">
                                        <p className="mb-1 text-sm font-medium text-[#575754]">
                                            Fixed rate
                                        </p>
                                        <p className="text-3xl font-semibold text-[#1b1b18]">
                                            {formatCurrency(fixedRate, curr)}
                                        </p>
                                        <p className="mt-1 text-sm text-[#575754]">
                                            per card
                                        </p>
                                    </div>
                                )}
                            </div>
                        </section>
                    )}
                </main>
            </div>
        </>
    );
}
