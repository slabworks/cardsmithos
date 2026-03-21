import { Head, Link } from '@inertiajs/react';
import {
    CheckCircle,
    MessageSquare,
    Plus,
    Search,
    XCircle,
} from 'lucide-react';
import { useMemo, useState } from 'react';
import InquiryController from '@/actions/App/Http/Controllers/InquiryController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { create, index } from '@/routes/inquiries';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Inquiries', href: index() }];

type InquiryItem = {
    id: number;
    contact_username: string;
    communication_method: string;
    inquired_at: string;
    converted: boolean;
    customer: { id: number; name: string } | null;
};

export default function InquiriesIndex({
    inquiries,
}: {
    inquiries: InquiryItem[];
}) {
    const [search, setSearch] = useState('');
    const filtered = useMemo(() => {
        const q = search.toLowerCase();

        if (!q) {
            return inquiries;
        }

        return inquiries.filter(
            (i) =>
                i.contact_username.toLowerCase().includes(q) ||
                i.communication_method
                    ?.replace('_', ' ')
                    .toLowerCase()
                    .includes(q) ||
                i.customer?.name.toLowerCase().includes(q),
        );
    }, [inquiries, search]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Inquiries" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-xl font-semibold">Inquiries</h1>
                        {inquiries.length > 0 && (
                            <p className="text-sm text-muted-foreground">
                                {inquiries.filter((i) => i.converted).length} of{' '}
                                {inquiries.length} converted
                            </p>
                        )}
                    </div>
                    <Button asChild>
                        <Link href={create()}>
                            <Plus className="mr-2 size-4" />
                            Add inquiry
                        </Link>
                    </Button>
                </div>
                {inquiries.length > 0 && (
                    <div className="relative">
                        <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            placeholder="Search inquiries..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            className="pl-9"
                        />
                    </div>
                )}
                <div className="rounded-lg border border-sidebar-border bg-card">
                    {inquiries.length === 0 ? (
                        <div className="flex flex-col items-center justify-center gap-2 py-12 text-center">
                            <MessageSquare className="size-10 text-muted-foreground" />
                            <p className="text-sm text-muted-foreground">
                                No inquiries yet
                            </p>
                            <Button asChild variant="outline">
                                <Link href={create()}>
                                    Add your first inquiry
                                </Link>
                            </Button>
                        </div>
                    ) : filtered.length === 0 ? (
                        <div className="flex flex-col items-center justify-center gap-2 py-12 text-center">
                            <p className="text-sm text-muted-foreground">
                                No inquiries match your search
                            </p>
                        </div>
                    ) : (
                        <ul className="divide-y divide-sidebar-border">
                            {filtered.map((inquiry) => (
                                <li key={inquiry.id}>
                                    <Link
                                        href={InquiryController.show.url(
                                            inquiry,
                                        )}
                                        className="flex items-center justify-between px-4 py-3 hover:bg-muted/50"
                                    >
                                        <div className="flex flex-col gap-0.5">
                                            <span className="font-medium">
                                                {inquiry.contact_username}
                                            </span>
                                            <span className="text-sm text-muted-foreground">
                                                {new Date(
                                                    inquiry.inquired_at,
                                                ).toLocaleDateString(
                                                    undefined,
                                                    {
                                                        dateStyle: 'medium',
                                                    },
                                                )}
                                                {inquiry.customer && (
                                                    <>
                                                        {' '}
                                                        &middot;{' '}
                                                        {inquiry.customer.name}
                                                    </>
                                                )}
                                            </span>
                                        </div>
                                        <div className="flex items-center gap-3">
                                            <Badge variant="secondary">
                                                {inquiry.communication_method.replace(
                                                    '_',
                                                    ' ',
                                                )}
                                            </Badge>
                                            {inquiry.converted ? (
                                                <CheckCircle className="size-4 text-emerald-500" />
                                            ) : (
                                                <XCircle className="size-4 text-muted-foreground" />
                                            )}
                                        </div>
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
