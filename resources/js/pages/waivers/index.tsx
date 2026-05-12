import { Form, Head } from '@inertiajs/react';
import { Copy, FileText, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

type ServiceWaiver = {
    id: number;
    expires_at: string;
    signed_at: string | null;
    signer_name: string | null;
    signer_email: string | null;
    signed_url: string | null;
};

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Waivers', href: '/waivers' }];

const formatDate = (value: string) =>
    new Date(value).toLocaleDateString(undefined, { dateStyle: 'medium' });

const signedDescription = (waiver: ServiceWaiver) => {
    const signer = [waiver.signer_name, waiver.signer_email]
        .filter(Boolean)
        .join(' - ');

    return signer
        ? `Signed by ${signer} on ${formatDate(waiver.signed_at ?? '')}`
        : `Signed on ${formatDate(waiver.signed_at ?? '')}`;
};

export default function WaiversIndex({
    waivers,
}: {
    waivers: ServiceWaiver[];
}) {
    const [copiedWaiverId, setCopiedWaiverId] = useState<number | null>(null);

    const copyWaiverUrl = (waiver: ServiceWaiver) => {
        if (!waiver.signed_url) {
            return;
        }

        void navigator.clipboard.writeText(waiver.signed_url).then(() => {
            setCopiedWaiverId(waiver.id);
            setTimeout(() => setCopiedWaiverId(null), 2000);
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Waivers" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h1 className="text-xl font-semibold">Waivers</h1>
                        <p className="text-sm text-muted-foreground">
                            Create standalone waiver links before adding a
                            paying customer.
                        </p>
                    </div>
                    <Form action="/waivers" method="post">
                        {({ processing }) => (
                            <Button type="submit" disabled={processing}>
                                <Plus className="mr-2 size-4" />
                                Create waiver
                            </Button>
                        )}
                    </Form>
                </div>

                <div className="overflow-hidden rounded-lg border border-sidebar-border bg-card">
                    {waivers.length === 0 ? (
                        <div className="flex flex-col items-center justify-center gap-2 py-12 text-center">
                            <FileText className="size-10 text-muted-foreground" />
                            <p className="text-sm text-muted-foreground">
                                No waivers yet
                            </p>
                        </div>
                    ) : (
                        <ul className="divide-y divide-sidebar-border">
                            {waivers.map((waiver) => (
                                <li key={waiver.id} className="space-y-3 p-4">
                                    <div className="flex flex-wrap items-start justify-between gap-3">
                                        <div className="min-w-0">
                                            <div className="flex items-center gap-2">
                                                <span className="font-medium">
                                                    Waiver #{waiver.id}
                                                </span>
                                                <Badge
                                                    variant={
                                                        waiver.signed_at
                                                            ? 'default'
                                                            : 'outline'
                                                    }
                                                >
                                                    {waiver.signed_at
                                                        ? 'Signed'
                                                        : 'Unsigned'}
                                                </Badge>
                                            </div>
                                            <p className="mt-1 text-sm text-muted-foreground">
                                                {waiver.signed_at
                                                    ? signedDescription(waiver)
                                                    : `Expires ${formatDate(waiver.expires_at)}`}
                                            </p>
                                        </div>
                                        <Dialog>
                                            <DialogTrigger asChild>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                >
                                                    <Trash2 className="mr-1 size-4" />
                                                    Delete
                                                </Button>
                                            </DialogTrigger>
                                            <DialogContent>
                                                <DialogTitle>
                                                    Delete waiver #{waiver.id}?
                                                </DialogTitle>
                                                <DialogDescription>
                                                    This permanently removes the
                                                    waiver link
                                                    {waiver.signed_at
                                                        ? ' and its signature record'
                                                        : ''}
                                                    . This cannot be undone.
                                                </DialogDescription>
                                                <Form
                                                    action={`/waivers/${waiver.id}`}
                                                    method="delete"
                                                    className="mt-4"
                                                >
                                                    {({ processing }) => (
                                                        <DialogFooter className="gap-2">
                                                            <DialogClose
                                                                asChild
                                                            >
                                                                <Button
                                                                    type="button"
                                                                    variant="secondary"
                                                                >
                                                                    Cancel
                                                                </Button>
                                                            </DialogClose>
                                                            <Button
                                                                type="submit"
                                                                variant="destructive"
                                                                disabled={
                                                                    processing
                                                                }
                                                            >
                                                                Delete waiver
                                                            </Button>
                                                        </DialogFooter>
                                                    )}
                                                </Form>
                                            </DialogContent>
                                        </Dialog>
                                    </div>

                                    {waiver.signed_url && (
                                        <div className="flex flex-wrap items-center gap-2 sm:flex-nowrap">
                                            <Input
                                                readOnly
                                                value={waiver.signed_url}
                                                className="min-w-0 font-mono text-sm"
                                            />
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                onClick={() =>
                                                    copyWaiverUrl(waiver)
                                                }
                                            >
                                                <Copy className="mr-1 size-4" />
                                                {copiedWaiverId === waiver.id
                                                    ? 'Copied'
                                                    : 'Copy'}
                                            </Button>
                                        </div>
                                    )}
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
