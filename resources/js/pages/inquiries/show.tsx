import { Head, Link } from '@inertiajs/react';
import { CheckCircle, Pencil, XCircle } from 'lucide-react';
import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import InquiryController from '@/actions/App/Http/Controllers/InquiryController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { index } from '@/routes/inquiries';
import type { BreadcrumbItem } from '@/types';

export default function InquiriesShow({
    inquiry,
}: {
    inquiry: {
        id: number;
        contact_username: string;
        communication_method: string;
        inquired_at: string;
        converted: boolean;
        notes: string | null;
        customer: { id: number; name: string } | null;
    };
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Inquiries', href: index() },
        {
            title: inquiry.contact_username,
            href: InquiryController.show.url(inquiry),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={inquiry.contact_username} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-xl font-semibold">
                            {inquiry.contact_username}
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            {new Date(inquiry.inquired_at).toLocaleDateString(
                                undefined,
                                { dateStyle: 'long' },
                            )}
                        </p>
                    </div>
                    <Button asChild variant="outline">
                        <Link href={InquiryController.edit.url(inquiry)}>
                            <Pencil className="mr-2 size-4" />
                            Edit
                        </Link>
                    </Button>
                </div>

                <div className="max-w-xl space-y-4">
                    <div className="rounded-lg border border-sidebar-border bg-card p-4">
                        <dl className="grid gap-4">
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">
                                    Communication method
                                </dt>
                                <dd>
                                    <Badge variant="secondary">
                                        {inquiry.communication_method.replace(
                                            '_',
                                            ' ',
                                        )}
                                    </Badge>
                                </dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">
                                    Converted
                                </dt>
                                <dd className="flex items-center gap-1.5">
                                    {inquiry.converted ? (
                                        <>
                                            <CheckCircle className="size-4 text-emerald-500" />
                                            <span className="text-sm">Yes</span>
                                        </>
                                    ) : (
                                        <>
                                            <XCircle className="size-4 text-muted-foreground" />
                                            <span className="text-sm">No</span>
                                        </>
                                    )}
                                </dd>
                            </div>
                            {inquiry.customer && (
                                <div>
                                    <dt className="text-sm font-medium text-muted-foreground">
                                        Customer
                                    </dt>
                                    <dd>
                                        <Link
                                            href={CustomerController.show.url(
                                                inquiry.customer,
                                            )}
                                            className="text-sm text-primary underline-offset-4 hover:underline"
                                        >
                                            {inquiry.customer.name}
                                        </Link>
                                    </dd>
                                </div>
                            )}
                            {inquiry.notes && (
                                <div>
                                    <dt className="text-sm font-medium text-muted-foreground">
                                        Notes
                                    </dt>
                                    <dd className="text-sm whitespace-pre-wrap">
                                        {inquiry.notes}
                                    </dd>
                                </div>
                            )}
                        </dl>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
