import { Head, Link } from '@inertiajs/react';
import { ClipboardList, Plus, Search } from 'lucide-react';
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
        cold_lead: 'secondary',
        warm_lead: 'secondary',
        hot_lead: 'default',
        in_progress: 'default',
        inactive: 'outline',
    };

type SubmissionItem = {
    id: number;
    status: string | null;
    referral_source: string | null;
    customer: {
        name: string;
        email: string | null;
    };
};

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
                  submission.customer.email?.toLowerCase().includes(query) ||
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
                            {filtered.map((submission) => (
                                <li key={submission.id}>
                                    <Link
                                        href={SubmissionController.show.url(
                                            submission,
                                        )}
                                        className="flex items-center justify-between px-4 py-3 hover:bg-muted/50"
                                    >
                                        <div className="flex flex-col gap-0.5">
                                            <span className="font-medium">
                                                {submission.customer.name}
                                            </span>
                                            {submission.customer.email && (
                                                <span className="text-sm text-muted-foreground">
                                                    {submission.customer.email}
                                                </span>
                                            )}
                                        </div>
                                        {submission.status && (
                                            <Badge
                                                variant={
                                                    statusBadgeVariant[
                                                        submission.status
                                                    ] ?? 'outline'
                                                }
                                            >
                                                {submission.status.replace(
                                                    '_',
                                                    ' ',
                                                )}
                                            </Badge>
                                        )}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
