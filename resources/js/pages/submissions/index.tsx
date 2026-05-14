import { Head, Link } from '@inertiajs/react';
import { ClipboardList, Plus, Search, TriangleAlert } from 'lucide-react';
import { useState } from 'react';
import SubmissionController from '@/actions/App/Http/Controllers/SubmissionController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { create, index } from '@/routes/submissions';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Submissions', href: index() }];

const statusBadgeVariant: Record<string, 'default' | 'secondary' | 'outline'> =
    {
        pending: 'secondary',
        in_progress: 'default',
        complete: 'default',
        cancelled: 'outline',
    };

type SubmissionItem = {
    id: number;
    status: string | null;
    referral_source: string | null;
    cards_count: number;
    payments_count: number;
    shipments_count: number;
    customer: {
        name: string;
        contact_detail: string | null;
    };
};

const missingRequirements = (submission: SubmissionItem) =>
    [
        submission.payments_count === 0 ? 'payment' : null,
        submission.shipments_count === 0 ? 'shipping' : null,
        submission.cards_count === 0 ? 'cards' : null,
    ].filter((requirement): requirement is string => requirement !== null);

const missingRequirementBadgeClassName =
    'border-muted-foreground/20 bg-muted/40 px-1.5 text-muted-foreground';

export default function SubmissionsIndex({
    submissions,
}: {
    submissions: SubmissionItem[];
}) {
    const [search, setSearch] = useState('');
    const query = search.toLowerCase();
    const filtered = query
        ? submissions.filter(
              (submission) =>
                  submission.customer.name.toLowerCase().includes(query) ||
                  submission.customer.contact_detail
                      ?.toLowerCase()
                      .includes(query) ||
                  submission.status?.replace('_', ' ').includes(query) ||
                  submission.referral_source?.toLowerCase().includes(query),
          )
        : submissions;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Submissions" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-xl font-semibold">Submissions</h1>
                    <Button asChild>
                        <Link href={create()}>
                            <Plus className="mr-2 size-4" />
                            Add submission
                        </Link>
                    </Button>
                </div>
                {submissions.length > 0 && (
                    <div className="relative">
                        <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            placeholder="Search submissions..."
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            className="pl-9"
                        />
                    </div>
                )}
                <div className="rounded-lg border border-sidebar-border bg-card">
                    {submissions.length === 0 ? (
                        <div className="flex flex-col items-center justify-center gap-2 py-12 text-center">
                            <ClipboardList className="size-10 text-muted-foreground" />
                            <p className="text-sm text-muted-foreground">
                                No submissions yet
                            </p>
                            <Button asChild variant="outline">
                                <Link href={create()}>
                                    Add your first submission
                                </Link>
                            </Button>
                        </div>
                    ) : filtered.length === 0 ? (
                        <div className="flex flex-col items-center justify-center gap-2 py-12 text-center">
                            <p className="text-sm text-muted-foreground">
                                No submissions match your search
                            </p>
                        </div>
                    ) : (
                        <ul className="divide-y divide-sidebar-border">
                            {filtered.map((submission) => {
                                const missing = missingRequirements(submission);

                                return (
                                    <li key={submission.id}>
                                        <Link
                                            href={SubmissionController.show.url(
                                                submission,
                                            )}
                                            className="flex items-center justify-between gap-3 px-4 py-3 hover:bg-muted/50"
                                        >
                                            <div className="flex min-w-0 flex-col gap-0.5">
                                                <span className="truncate font-medium">
                                                    {submission.customer.name}
                                                </span>
                                                {submission.customer
                                                    .contact_detail && (
                                                    <span className="truncate text-sm text-muted-foreground">
                                                        {
                                                            submission.customer
                                                                .contact_detail
                                                        }
                                                    </span>
                                                )}
                                            </div>
                                            <div className="flex shrink-0 flex-wrap items-center justify-end gap-2">
                                                {missing.map((requirement) => (
                                                    <Badge
                                                        key={requirement}
                                                        variant="outline"
                                                        className={
                                                            missingRequirementBadgeClassName
                                                        }
                                                        title={`Required: ${requirement}`}
                                                    >
                                                        <TriangleAlert />
                                                        {requirement}
                                                    </Badge>
                                                ))}
                                                {submission.status && (
                                                    <Badge
                                                        variant={
                                                            statusBadgeVariant[
                                                                submission
                                                                    .status
                                                            ] ?? 'outline'
                                                        }
                                                    >
                                                        {submission.status.replace(
                                                            '_',
                                                            ' ',
                                                        )}
                                                    </Badge>
                                                )}
                                            </div>
                                        </Link>
                                    </li>
                                );
                            })}
                        </ul>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
