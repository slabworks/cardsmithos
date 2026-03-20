import { Head, Link, usePage } from '@inertiajs/react';
import { dashboard, login, register } from '@/routes';

const GITHUB_URL = 'https://github.com/slabworks/cardsmithos';
const GITHUB_CONTRIBUTING = `${GITHUB_URL}#contributing`;
const PATREON_URL = 'https://www.patreon.com/c/CardSmithOS';
const DISCORD_URL = 'https://discord.gg/ycBacKEyhW';

function ExternalIcon() {
    return (
        <svg
            className="h-3.5 w-3.5 shrink-0"
            viewBox="0 0 10 11"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path
                d="M7.70833 6.95834V2.79167H3.54167M2.5 8L7.5 3.00001"
                stroke="currentColor"
                strokeLinecap="square"
            />
        </svg>
    );
}

export default function Welcome({
    canRegister = true,
}: {
    canRegister?: boolean;
}) {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Cardsmith OS — Trading card repair shop CRM">
                <meta
                    name="description"
                    content="Open-source CRM for trading card repair shops. Track submissions, manage jobs, and keep your shop organized—without lock-in or subscription fees."
                />
                <meta
                    property="og:title"
                    content="Cardsmith OS — Trading card repair shop CRM"
                />
                <meta
                    property="og:description"
                    content="Open-source CRM for trading card repair shops. Track submissions, manage jobs, and keep your shop organized—without lock-in or subscription fees."
                />
                <meta name="twitter:card" content="summary_large_image" />
                <meta
                    name="twitter:title"
                    content="Cardsmith OS — Trading card repair shop CRM"
                />
                <meta
                    name="twitter:description"
                    content="Open-source CRM for trading card repair shops. Track submissions, manage jobs, and keep your shop organized—without lock-in or subscription fees."
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
                            Cardsmith OS
                        </span>
                        <nav className="flex items-center gap-3 text-sm">
                            <a
                                href={GITHUB_URL}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="inline-flex items-center gap-1 rounded px-2.5 py-1.5 text-[#575754] hover:bg-[#f5f5f4] hover:text-[#1b1b18]"
                            >
                                GitHub
                                <ExternalIcon />
                            </a>
                            <a
                                href={PATREON_URL}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="inline-flex items-center gap-1 rounded px-2.5 py-1.5 text-[#575754] hover:bg-[#f5f5f4] hover:text-[#1b1b18]"
                            >
                                Patreon
                                <ExternalIcon />
                            </a>
                            <a
                                href={DISCORD_URL}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="inline-flex items-center gap-1 rounded px-2.5 py-1.5 text-[#575754] hover:bg-[#f5f5f4] hover:text-[#1b1b18]"
                            >
                                Discord
                                <ExternalIcon />
                            </a>
                            <span
                                className="h-4 w-px bg-[#e3e3e0]"
                                aria-hidden
                            />
                            {auth.user ? (
                                <Link
                                    href={dashboard()}
                                    className="inline-block rounded border border-[#1b1b18] bg-[#1b1b18] px-4 py-2 font-medium text-white hover:bg-[#0a0a0a]"
                                >
                                    Dashboard
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={login()}
                                        className="inline-block rounded border border-transparent px-4 py-2 font-medium text-[#575754] hover:text-[#1b1b18]"
                                    >
                                        Log in
                                    </Link>
                                    {canRegister && (
                                        <Link
                                            href={register()}
                                            className="inline-block rounded border border-[#1b1b18] bg-[#1b1b18] px-4 py-2 font-medium text-white hover:bg-[#0a0a0a]"
                                        >
                                            Register
                                        </Link>
                                    )}
                                </>
                            )}
                        </nav>
                    </div>
                </header>

                <main className="mx-auto max-w-4xl px-6 py-12 lg:px-8 lg:py-16">
                    <section className="mb-16 text-center">
                        <h1 className="mb-4 text-3xl font-semibold tracking-tight text-[#1b1b18] lg:text-4xl">
                            Run your trading card repair shop in one place
                        </h1>
                        <p className="mx-auto max-w-2xl text-lg leading-relaxed text-[#575754]">
                            Cardsmith OS is an open-source CRM for repair shops.
                            Track submissions, manage jobs, and keep your shop
                            organized—without lock-in or subscription fees.
                        </p>
                        {auth.user ? (
                            <p className="mt-6">
                                <Link
                                    href={dashboard()}
                                    className="inline-block rounded border border-[#1b1b18] bg-[#1b1b18] px-6 py-3 font-medium text-white hover:bg-[#0a0a0a]"
                                >
                                    Go to Dashboard
                                </Link>
                            </p>
                        ) : (
                            <p className="mt-6 flex flex-wrap items-center justify-center gap-3">
                                <Link
                                    href={register()}
                                    className="inline-block rounded border border-[#1b1b18] bg-[#1b1b18] px-6 py-3 font-medium text-white hover:bg-[#0a0a0a]"
                                >
                                    Get started
                                </Link>
                                <Link
                                    href={login()}
                                    className="inline-block rounded border border-[#e3e3e0] bg-white px-6 py-3 font-medium text-[#1b1b18] hover:bg-[#f5f5f4]"
                                >
                                    Log in
                                </Link>
                            </p>
                        )}
                    </section>

                    <section className="mb-16">
                        <h2 className="mb-8 text-center text-xl font-medium text-[#1b1b18]">
                            What you get
                        </h2>
                        <div className="grid gap-6 sm:grid-cols-3">
                            <div className="rounded-lg border border-[#e8e8e6] bg-[#fafaf9] p-6">
                                <h3 className="mb-2 font-medium text-[#1b1b18]">
                                    Submissions
                                </h3>
                                <p className="text-sm leading-relaxed text-[#575754]">
                                    Accept and track customer submissions from
                                    intake through completion. Keep notes,
                                    photos, and status in one place. Estimate
                                    fees from restoration hours and your hourly
                                    rate.
                                </p>
                            </div>
                            <div className="rounded-lg border border-[#e8e8e6] bg-[#fafaf9] p-6">
                                <h3 className="mb-2 font-medium text-[#1b1b18]">
                                    Job tracking
                                </h3>
                                <p className="text-sm leading-relaxed text-[#575754]">
                                    Manage repair jobs with clear statuses and
                                    workflows. See what’s in queue, in progress,
                                    and repaired.
                                </p>
                            </div>
                            <div className="rounded-lg border border-[#e8e8e6] bg-[#fafaf9] p-6">
                                <h3 className="mb-2 font-medium text-[#1b1b18]">
                                    Shop management
                                </h3>
                                <p className="text-sm leading-relaxed text-[#575754]">
                                    Set your hourly rate, company details, and
                                    tax. Built for repair shop operations.
                                    Modern stack: Laravel, React, Tailwind.
                                    Self-host or run it your way.
                                </p>
                            </div>
                        </div>
                    </section>

                    <section className="rounded-lg border border-[#e8e8e6] bg-[#fafaf9] p-6 lg:p-8">
                        <h2 className="mb-2 text-lg font-medium text-[#1b1b18]">
                            Open source
                        </h2>
                        <p className="mb-4 text-sm leading-relaxed text-[#575754]">
                            Cardsmith OS is free and open source (MIT). You can
                            contribute code, docs, or ideas—or support the
                            project on Patreon to help keep development going.
                        </p>
                        <p className="flex flex-wrap items-center gap-4 text-sm">
                            <a
                                href={GITHUB_CONTRIBUTING}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="inline-flex items-center gap-1.5 font-medium text-[#1b1b18] underline underline-offset-4 hover:no-underline"
                            >
                                Contributing guide
                                <ExternalIcon />
                            </a>
                            <a
                                href={GITHUB_URL}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="inline-flex items-center gap-1.5 font-medium text-[#1b1b18] underline underline-offset-4 hover:no-underline"
                            >
                                GitHub
                                <ExternalIcon />
                            </a>
                            <a
                                href={PATREON_URL}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="inline-flex items-center gap-1.5 font-medium text-[#1b1b18] underline underline-offset-4 hover:no-underline"
                            >
                                Support on Patreon
                                <ExternalIcon />
                            </a>
                            <a
                                href={DISCORD_URL}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="inline-flex items-center gap-1.5 font-medium text-[#1b1b18] underline underline-offset-4 hover:no-underline"
                            >
                                Discord
                                <ExternalIcon />
                            </a>
                        </p>
                    </section>
                </main>
            </div>
        </>
    );
}
