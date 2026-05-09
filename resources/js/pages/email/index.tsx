import { Form, Head, Link } from '@inertiajs/react';
import { Mail, RefreshCw, UserPlus } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

type Customer = {
    id: number;
    name: string;
    email: string | null;
};

type InboxContact = {
    email: string;
    name: string | null;
    latestMessage: {
        subject: string | null;
        snippet: string | null;
        sentAt: string | null;
    };
    customer: Customer | null;
};

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Email', href: '/email' }];

export default function EmailIndex({
    gmailAccount,
    contacts,
}: {
    gmailAccount: { email: string | null; lastSyncedAt: string | null } | null;
    contacts: InboxContact[];
}) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Email" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 className="text-xl font-semibold">Email</h1>
                        <p className="text-sm text-muted-foreground">
                            People who have emailed your connected Gmail inbox.
                        </p>
                        {gmailAccount && (
                            <p className="mt-1 text-sm text-muted-foreground">
                                Connected as {gmailAccount.email}
                            </p>
                        )}
                    </div>
                    {gmailAccount && (
                        <Form action="/email/sync" method="post">
                            <Button type="submit" variant="outline">
                                <RefreshCw className="mr-2 size-4" />
                                Sync inbox
                            </Button>
                        </Form>
                    )}
                </div>

                {!gmailAccount ? (
                    <div className="rounded-lg border border-sidebar-border bg-card p-8 text-center">
                        <Mail className="mx-auto mb-3 size-10 text-muted-foreground" />
                        <h2 className="font-medium">Connect Gmail</h2>
                        <p className="mx-auto mt-1 max-w-md text-sm text-muted-foreground">
                            Connect Gmail to see people who have reached out and
                            convert promising conversations into customers.
                        </p>
                        <Button className="mt-4" asChild>
                            <Link href="/settings/integrations">
                                Open integrations
                            </Link>
                        </Button>
                    </div>
                ) : contacts.length === 0 ? (
                    <div className="rounded-lg border border-sidebar-border bg-card p-8 text-center">
                        <Mail className="mx-auto mb-3 size-10 text-muted-foreground" />
                        <h2 className="font-medium">No inbox contacts yet</h2>
                        <p className="mx-auto mt-1 max-w-md text-sm text-muted-foreground">
                            Run sync after connecting Gmail. CardsmithOS will
                            list senders from synced inbound inbox messages.
                        </p>
                    </div>
                ) : (
                    <div className="rounded-lg border border-sidebar-border bg-card">
                        <div className="border-b border-sidebar-border px-4 py-3">
                            <h2 className="font-medium">Inbox contacts</h2>
                        </div>
                        <ul className="divide-y divide-sidebar-border">
                            {contacts.map((contact) => (
                                <li
                                    key={contact.email}
                                    className="flex flex-col gap-3 px-4 py-4 md:flex-row md:items-start md:justify-between"
                                >
                                    <div className="min-w-0 flex-1">
                                        <div className="flex flex-wrap items-center gap-2">
                                            <h3 className="font-medium">
                                                {contact.name || contact.email}
                                            </h3>
                                            {contact.customer && (
                                                <Badge variant="outline">
                                                    Customer
                                                </Badge>
                                            )}
                                        </div>
                                        <p className="mt-0.5 text-sm text-muted-foreground">
                                            {contact.email}
                                        </p>
                                        <div className="mt-3 max-w-3xl">
                                            <p className="truncate text-sm font-medium">
                                                {contact.latestMessage.subject ||
                                                    '(No subject)'}
                                            </p>
                                            {contact.latestMessage.snippet && (
                                                <p className="mt-1 line-clamp-2 text-sm text-muted-foreground">
                                                    {
                                                        contact.latestMessage
                                                            .snippet
                                                    }
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                    <div className="flex shrink-0 justify-start md:justify-end">
                                        {contact.customer ? (
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                asChild
                                            >
                                                <Link
                                                    href={`/customers/${contact.customer.id}`}
                                                >
                                                    View customer
                                                </Link>
                                            </Button>
                                        ) : (
                                            <Form
                                                action="/email/contacts/convert"
                                                method="post"
                                            >
                                                <input
                                                    type="hidden"
                                                    name="email"
                                                    value={contact.email}
                                                />
                                                <Button type="submit" size="sm">
                                                    <UserPlus className="mr-2 size-4" />
                                                    Convert to customer
                                                </Button>
                                            </Form>
                                        )}
                                    </div>
                                </li>
                            ))}
                        </ul>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
