import { Head, Link, usePage } from '@inertiajs/react';
import { dashboard, login, register } from '@/routes';
import { index as storefrontIndex } from '@/routes/storefront';

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
            <Head title="Cardsmith OS — Trading Card Repair CRM & Management Software">
                <meta
                    name="description"
                    content="Trading card repair CRM and management software. Track repair submissions, manage jobs, and run your trading card repair shop—open-source, no lock-in or subscription fees."
                />
                <meta
                    name="keywords"
                    content="trading card repair crm, trading card repair management, card repair shop software, trading card restoration, card grading preparation, card repair tracking"
                />
                <meta
                    property="og:title"
                    content="Cardsmith OS — Trading Card Repair CRM & Management Software"
                />
                <meta
                    property="og:description"
                    content="Trading card repair CRM and management software. Track repair submissions, manage jobs, and run your trading card repair shop—open-source, no lock-in or subscription fees."
                />
                <meta name="twitter:card" content="summary_large_image" />
                <meta
                    name="twitter:title"
                    content="Cardsmith OS — Trading Card Repair CRM & Management Software"
                />
                <meta
                    name="twitter:description"
                    content="Trading card repair CRM and management software. Track repair submissions, manage jobs, and run your trading card repair shop—open-source, no lock-in or subscription fees."
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
                    {/* Hero */}
                    <section className="mb-16 text-center">
                        <h1 className="mb-4 text-3xl font-semibold tracking-tight text-[#1b1b18] lg:text-4xl">
                            Trading Card Repair CRM & Management Software
                        </h1>
                        <p className="mx-auto max-w-2xl text-lg leading-relaxed text-[#575754]">
                            Cardsmith OS is an open-source platform built for
                            trading card restoration shops. Manage every job
                            from intake to shipment, track finances, and let
                            customers follow their card’s progress in
                            real time—all without lock-in or subscription fees.
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
                        <p className="mt-4">
                            <Link
                                href={storefrontIndex.url()}
                                className="inline-block rounded border border-[#e3e3e0] bg-white px-6 py-3 font-medium text-[#1b1b18] hover:bg-[#f5f5f4]"
                            >
                                Find a card repair shop near you
                            </Link>
                        </p>
                    </section>

                    {/* Core features */}
                    <section className="mb-16">
                        <h2 className="mb-8 text-center text-xl font-medium text-[#1b1b18]">
                            Everything you need to run a card repair shop
                        </h2>
                        <div className="grid gap-6 sm:grid-cols-3">
                            <div className="rounded-lg border border-[#e8e8e6] bg-[#fafaf9] p-6">
                                <h3 className="mb-2 font-medium text-[#1b1b18]">
                                    Repair job tracking
                                </h3>
                                <p className="text-sm leading-relaxed text-[#575754]">
                                    Create repair jobs for each card and move
                                    them through Backlog, Pending, In Progress,
                                    and Repaired stages on a kanban board.
                                    Record before/after condition, restoration
                                    hours, notes, and photos.
                                </p>
                            </div>
                            <div className="rounded-lg border border-[#e8e8e6] bg-[#fafaf9] p-6">
                                <h3 className="mb-2 font-medium text-[#1b1b18]">
                                    Customer management
                                </h3>
                                <p className="text-sm leading-relaxed text-[#575754]">
                                    Keep a full profile for every customer with
                                    contact info, lead status, referral source,
                                    and lifetime value. See all their cards,
                                    payments, and shipments in one place.
                                </p>
                            </div>
                            <div className="rounded-lg border border-[#e8e8e6] bg-[#fafaf9] p-6">
                                <h3 className="mb-2 font-medium text-[#1b1b18]">
                                    Lead & inquiry tracking
                                </h3>
                                <p className="text-sm leading-relaxed text-[#575754]">
                                    Log every incoming inquiry, track how
                                    customers found you, and measure your
                                    conversion rate. Turn leads into customers
                                    with a single click.
                                </p>
                            </div>
                            <div className="rounded-lg border border-[#e8e8e6] bg-[#fafaf9] p-6">
                                <h3 className="mb-2 font-medium text-[#1b1b18]">
                                    Pricing & invoicing
                                </h3>
                                <p className="text-sm leading-relaxed text-[#575754]">
                                    Set your hourly rate and tax, then let fees
                                    calculate automatically from restoration
                                    hours. Use the built-in pricing calculator
                                    for quick quotes, and generate PDF invoices
                                    for customers.
                                </p>
                            </div>
                            <div className="rounded-lg border border-[#e8e8e6] bg-[#fafaf9] p-6">
                                <h3 className="mb-2 font-medium text-[#1b1b18]">
                                    Payments & expenses
                                </h3>
                                <p className="text-sm leading-relaxed text-[#575754]">
                                    Record payments by method (cash, card,
                                    PayPal, and more) and track business
                                    expenses across categories like supplies,
                                    equipment, and shipping. See your revenue
                                    and costs at a glance.
                                </p>
                            </div>
                            <div className="rounded-lg border border-[#e8e8e6] bg-[#fafaf9] p-6">
                                <h3 className="mb-2 font-medium text-[#1b1b18]">
                                    Shipping & fulfillment
                                </h3>
                                <p className="text-sm leading-relaxed text-[#575754]">
                                    Record shipments with carrier tracking
                                    numbers and shipping costs. Know which cards
                                    have been returned and how much you’ve spent
                                    on postage.
                                </p>
                            </div>
                        </div>
                    </section>

                    {/* Customer-facing features */}
                    <section className="mb-16">
                        <h2 className="mb-8 text-center text-xl font-medium text-[#1b1b18]">
                            Keep your customers in the loop
                        </h2>
                        <div className="grid gap-6 sm:grid-cols-2">
                            <div className="rounded-lg border border-[#e8e8e6] bg-[#fafaf9] p-6">
                                <h3 className="mb-2 font-medium text-[#1b1b18]">
                                    Shareable progress timelines
                                </h3>
                                <p className="text-sm leading-relaxed text-[#575754]">
                                    Each card gets a unique timeline link you
                                    can share with the customer. They can see
                                    milestones, activity updates, and photos of
                                    their card’s restoration progress—no login
                                    required.
                                </p>
                            </div>
                            <div className="rounded-lg border border-[#e8e8e6] bg-[#fafaf9] p-6">
                                <h3 className="mb-2 font-medium text-[#1b1b18]">
                                    Digital service waivers
                                </h3>
                                <p className="text-sm leading-relaxed text-[#575754]">
                                    Generate waiver links for customers to sign
                                    digitally. Waivers are tracked with IP
                                    address and expiration date, keeping your
                                    shop legally protected without paper
                                    hassle.
                                </p>
                            </div>
                        </div>
                    </section>

                    {/* Shop directory */}
                    <section className="mb-16 rounded-lg border border-[#e8e8e6] bg-[#fafaf9] p-6 lg:p-8">
                        <h2 className="mb-2 text-lg font-medium text-[#1b1b18]">
                            Public shop directory
                        </h2>
                        <p className="mb-4 text-sm leading-relaxed text-[#575754]">
                            Opt in to list your shop in the Cardsmith OS
                            directory. Collectors can search by name, location,
                            or hourly rate to find a repair shop near them. Each
                            listing shows your bio, rates, and social links—free
                            exposure for your business.
                        </p>
                        <p>
                            <Link
                                href={storefrontIndex.url()}
                                className="inline-flex items-center gap-1.5 text-sm font-medium text-[#1b1b18] underline underline-offset-4 hover:no-underline"
                            >
                                Browse the directory
                            </Link>
                        </p>
                    </section>

                    {/* Dashboard callout */}
                    <section className="mb-16 rounded-lg border border-[#e8e8e6] bg-[#fafaf9] p-6 lg:p-8">
                        <h2 className="mb-2 text-lg font-medium text-[#1b1b18]">
                            Dashboard & analytics
                        </h2>
                        <p className="text-sm leading-relaxed text-[#575754]">
                            See your shop’s health at a glance. The dashboard
                            shows total revenue, shipping costs, expenses, and
                            lead conversion rate alongside a 12-month revenue
                            chart. A kanban board surfaces every card in your
                            pipeline so nothing slips through the cracks.
                        </p>
                    </section>

                    {/* How it works */}
                    <section className="mb-16">
                        <h2 className="mb-8 text-center text-xl font-medium text-[#1b1b18]">
                            How it works
                        </h2>
                        <div className="grid gap-6 sm:grid-cols-4">
                            <div className="text-center">
                                <div className="mx-auto mb-3 flex h-8 w-8 items-center justify-center rounded-full bg-[#1b1b18] text-sm font-medium text-white">
                                    1
                                </div>
                                <h3 className="mb-1 text-sm font-medium text-[#1b1b18]">
                                    Receive inquiry
                                </h3>
                                <p className="text-xs leading-relaxed text-[#575754]">
                                    Log the lead and how they found you.
                                </p>
                            </div>
                            <div className="text-center">
                                <div className="mx-auto mb-3 flex h-8 w-8 items-center justify-center rounded-full bg-[#1b1b18] text-sm font-medium text-white">
                                    2
                                </div>
                                <h3 className="mb-1 text-sm font-medium text-[#1b1b18]">
                                    Create job & waiver
                                </h3>
                                <p className="text-xs leading-relaxed text-[#575754]">
                                    Add the customer, send a waiver, and create
                                    repair cards.
                                </p>
                            </div>
                            <div className="text-center">
                                <div className="mx-auto mb-3 flex h-8 w-8 items-center justify-center rounded-full bg-[#1b1b18] text-sm font-medium text-white">
                                    3
                                </div>
                                <h3 className="mb-1 text-sm font-medium text-[#1b1b18]">
                                    Track & share progress
                                </h3>
                                <p className="text-xs leading-relaxed text-[#575754]">
                                    Update statuses, upload photos, and share
                                    the timeline link.
                                </p>
                            </div>
                            <div className="text-center">
                                <div className="mx-auto mb-3 flex h-8 w-8 items-center justify-center rounded-full bg-[#1b1b18] text-sm font-medium text-white">
                                    4
                                </div>
                                <h3 className="mb-1 text-sm font-medium text-[#1b1b18]">
                                    Invoice & ship
                                </h3>
                                <p className="text-xs leading-relaxed text-[#575754]">
                                    Generate an invoice, record payment, and log
                                    the shipment.
                                </p>
                            </div>
                        </div>
                    </section>

                    {/* Open source */}
                    <section className="rounded-lg border border-[#e8e8e6] bg-[#fafaf9] p-6 lg:p-8">
                        <h2 className="mb-2 text-lg font-medium text-[#1b1b18]">
                            Free & open source
                        </h2>
                        <p className="mb-4 text-sm leading-relaxed text-[#575754]">
                            Cardsmith OS is MIT-licensed. Self-host it on your
                            own server, own your data, and pay nothing. No
                            vendor lock-in, no monthly fees, no limits.
                            Contribute code, docs, or ideas—or support the
                            project on Patreon.
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
