import { Head, router, useForm } from '@inertiajs/react';
import {
    Link as LinkIcon,
    Loader2,
    Mail,
    MessageSquarePlus,
    Pencil,
    RefreshCw,
    Search,
    Send,
} from 'lucide-react';
import { useCallback, useRef, useState } from 'react';
import { toast } from 'sonner';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import {
    associate,
    createInquiry,
    index,
    reply,
    store,
    sync,
} from '@/routes/emails';
import { edit as editGmail } from '@/routes/gmail';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Email', href: index() }];

type EmailItem = {
    id: number;
    gmail_thread_id: string;
    direction: 'inbound' | 'outbound';
    from_address: string;
    from_name: string | null;
    to_addresses: string[];
    subject: string | null;
    snippet: string | null;
    is_read: boolean;
    received_at: string;
    customer: { id: number; name: string } | null;
};

type ThreadMessage = EmailItem & {
    body_html: string | null;
    resolved_body_html: string | null;
    body_text: string | null;
    cc_addresses: string[] | null;
    attachments: { id: number; filename: string; mime_type: string; size: number }[];
};

type CustomerOption = { id: number; name: string };

type PaginatedEmails = {
    data: EmailItem[];
    current_page: number;
    last_page: number;
    total: number;
};

export default function EmailsIndex({
    emails,
    selectedThread,
    customerOptions,
    filters,
    hasGmailAccount,
}: {
    emails: PaginatedEmails;
    selectedThread: ThreadMessage[] | null;
    customerOptions: CustomerOption[];
    filters: { search: string; customer_id: string; thread_id: string };
    hasGmailAccount: boolean;
}) {
    const [search, setSearch] = useState(filters.search);
    const [syncing, setSyncing] = useState(false);
    const searchTimeout = useRef<ReturnType<typeof setTimeout>>(undefined);

    const handleSearch = useCallback(
        (value: string) => {
            setSearch(value);
            clearTimeout(searchTimeout.current);
            searchTimeout.current = setTimeout(() => {
                router.get(
                    index.url(),
                    { search: value, customer_id: filters.customer_id },
                    { preserveState: true, replace: true },
                );
            }, 300);
        },
        [filters.customer_id],
    );

    const handleFilterCustomer = useCallback(
        (customerId: string) => {
            router.get(
                index.url(),
                {
                    search: filters.search,
                    customer_id: customerId === 'all' ? '' : customerId,
                },
                { preserveState: true, replace: true },
            );
        },
        [filters.search],
    );

    const selectThread = useCallback(
        (threadId: string) => {
            router.get(
                index.url(),
                {
                    search: filters.search,
                    customer_id: filters.customer_id,
                    thread_id: threadId,
                },
                { preserveState: false, replace: true },
            );
        },
        [filters.search, filters.customer_id],
    );

    if (!hasGmailAccount) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Email" />
                <div className="flex h-full flex-1 flex-col items-center justify-center gap-4 p-4">
                    <Mail className="size-12 text-muted-foreground" />
                    <div className="text-center">
                        <h2 className="text-lg font-semibold">
                            Connect your Gmail
                        </h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Connect your Gmail account to view and manage emails
                            from within Cardsmith OS.
                        </p>
                    </div>
                    <Button asChild>
                        <a href={editGmail.url()}>Go to Gmail settings</a>
                    </Button>
                </div>
            </AppLayout>
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Email" />
            <div className="flex h-[calc(100vh-4rem)] flex-col overflow-hidden">
                {/* Toolbar */}
                <div className="flex items-center gap-2 border-b px-4 py-2">
                    <div className="relative flex-1">
                        <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            placeholder="Search emails..."
                            value={search}
                            onChange={(e) => handleSearch(e.target.value)}
                            className="pl-9"
                        />
                    </div>
                    <Select
                        value={filters.customer_id || 'all'}
                        onValueChange={handleFilterCustomer}
                    >
                        <SelectTrigger className="w-[180px]">
                            <SelectValue placeholder="All customers" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All customers</SelectItem>
                            {customerOptions.map((c) => (
                                <SelectItem
                                    key={c.id}
                                    value={c.id.toString()}
                                >
                                    {c.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Button
                        variant="outline"
                        size="icon"
                        disabled={syncing}
                        onClick={() => {
                            setSyncing(true);
                            toast.info('Syncing emails, this may take a moment...');
                            router.post(sync.url(), {}, {
                                preserveState: true,
                                onSuccess: () => toast.success('Emails synced'),
                                onError: () => toast.error('Sync failed'),
                                onFinish: () => setSyncing(false),
                            });
                        }}
                        title="Sync now"
                    >
                        {syncing ? (
                            <Loader2 className="size-4 animate-spin" />
                        ) : (
                            <RefreshCw className="size-4" />
                        )}
                    </Button>
                    <ComposeDialog customerOptions={customerOptions} />
                </div>

                {/* Split pane */}
                <div className="flex flex-1 overflow-hidden">
                    {/* Email list */}
                    <div className="w-full max-w-md shrink-0 overflow-y-auto border-r">
                        {emails.data.length === 0 ? (
                            <div className="flex flex-col items-center justify-center gap-2 py-12 text-center">
                                <Mail className="size-10 text-muted-foreground" />
                                <p className="text-sm text-muted-foreground">
                                    {filters.search
                                        ? 'No emails match your search'
                                        : 'No emails yet. Emails will appear after the first sync.'}
                                </p>
                            </div>
                        ) : (
                            <>
                            <ul className="divide-y">
                                {emails.data.map((email) => {
                                    const isSelected =
                                        filters.thread_id ===
                                        email.gmail_thread_id;
                                    return (
                                        <li key={email.id}>
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    selectThread(
                                                        email.gmail_thread_id,
                                                    )
                                                }
                                                className={`w-full px-4 py-3 text-left hover:bg-muted/50 ${
                                                    isSelected
                                                        ? 'bg-muted'
                                                        : ''
                                                } ${!email.is_read ? 'font-semibold' : ''}`}
                                            >
                                                <div className="flex items-start justify-between gap-2">
                                                    <div className="min-w-0 flex-1">
                                                        <p className="truncate text-sm">
                                                            {email.from_name ||
                                                                email.from_address}
                                                        </p>
                                                        <p className="truncate text-sm text-foreground">
                                                            {email.subject ||
                                                                '(no subject)'}
                                                        </p>
                                                        <p className="truncate text-xs text-muted-foreground">
                                                            {email.snippet}
                                                        </p>
                                                    </div>
                                                    <div className="flex shrink-0 flex-col items-end gap-1">
                                                        <span className="text-xs text-muted-foreground">
                                                            {formatDate(
                                                                email.received_at,
                                                            )}
                                                        </span>
                                                        {email.customer && (
                                                            <Badge
                                                                variant="secondary"
                                                                className="text-xs"
                                                            >
                                                                {
                                                                    email
                                                                        .customer
                                                                        .name
                                                                }
                                                            </Badge>
                                                        )}
                                                        {!email.is_read && (
                                                            <div className="h-2 w-2 rounded-full bg-blue-500" />
                                                        )}
                                                    </div>
                                                </div>
                                            </button>
                                        </li>
                                    );
                                })}
                            </ul>
                            {emails.last_page > 1 && (
                                <div className="flex items-center justify-between border-t px-4 py-2">
                                    <span className="text-xs text-muted-foreground">
                                        Page {emails.current_page} of {emails.last_page} ({emails.total} emails)
                                    </span>
                                    <div className="flex gap-1">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            disabled={emails.current_page <= 1}
                                            onClick={() =>
                                                router.get(
                                                    index.url(),
                                                    {
                                                        search: filters.search,
                                                        customer_id: filters.customer_id,
                                                        page: emails.current_page - 1,
                                                    },
                                                    { preserveState: true, replace: true },
                                                )
                                            }
                                        >
                                            Prev
                                        </Button>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            disabled={emails.current_page >= emails.last_page}
                                            onClick={() =>
                                                router.get(
                                                    index.url(),
                                                    {
                                                        search: filters.search,
                                                        customer_id: filters.customer_id,
                                                        page: emails.current_page + 1,
                                                    },
                                                    { preserveState: true, replace: true },
                                                )
                                            }
                                        >
                                            Next
                                        </Button>
                                    </div>
                                </div>
                            )}
                            </>
                        )}
                    </div>

                    {/* Thread preview */}
                    <div className="flex-1 overflow-y-auto">
                        {selectedThread ? (
                            <ThreadView
                                messages={selectedThread}
                                customerOptions={customerOptions}
                            />
                        ) : (
                            <div className="flex h-full flex-col items-center justify-center gap-2 text-muted-foreground">
                                <Mail className="size-10" />
                                <p className="text-sm">
                                    Select an email to read
                                </p>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

function ThreadView({
    messages,
    customerOptions,
}: {
    messages: ThreadMessage[];
    customerOptions: CustomerOption[];
}) {
    const firstMessage = messages[0];
    const lastMessage = messages[messages.length - 1];
    const [associateCustomerId, setAssociateCustomerId] = useState('');

    const replyForm = useForm({
        body: '',
    });

    const handleReply = (e: React.FormEvent) => {
        e.preventDefault();
        replyForm.post(reply.url(lastMessage.id), {
            preserveScroll: true,
            onSuccess: () => replyForm.reset('body'),
        });
    };

    return (
        <div className="flex h-full flex-col">
            {/* Thread header */}
            <div className="border-b px-6 py-4">
                <div className="flex items-start justify-between gap-4">
                    <div className="min-w-0 flex-1">
                        <h2 className="text-lg font-semibold">
                            {firstMessage.subject || '(no subject)'}
                        </h2>
                        <p className="text-sm text-muted-foreground">
                            {messages.length} message
                            {messages.length > 1 ? 's' : ''} in thread
                        </p>
                    </div>
                    <div className="flex items-center gap-2">
                        {firstMessage.customer ? (
                            <Badge variant="secondary">
                                {firstMessage.customer.name}
                            </Badge>
                        ) : (
                            <div className="flex items-center gap-1">
                                <Select
                                    value={associateCustomerId}
                                    onValueChange={setAssociateCustomerId}
                                >
                                    <SelectTrigger className="w-[160px]">
                                        <SelectValue placeholder="Link to customer" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {customerOptions.map((c) => (
                                            <SelectItem
                                                key={c.id}
                                                value={c.id.toString()}
                                            >
                                                {c.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <Button
                                    variant="outline"
                                    size="icon"
                                    disabled={!associateCustomerId}
                                    onClick={() =>
                                        router.post(
                                            associate.url(firstMessage.id),
                                            {
                                                customer_id:
                                                    associateCustomerId,
                                            },
                                        )
                                    }
                                >
                                    <LinkIcon className="size-4" />
                                </Button>
                            </div>
                        )}
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() =>
                                router.post(
                                    createInquiry.url(firstMessage.id),
                                )
                            }
                        >
                            <MessageSquarePlus className="mr-1 size-4" />
                            Create inquiry
                        </Button>
                    </div>
                </div>
            </div>

            {/* Messages */}
            <div className="flex-1 space-y-4 overflow-y-auto px-6 py-4">
                {messages.map((msg) => (
                    <div
                        key={msg.id}
                        className="rounded-lg border bg-card p-4"
                    >
                        <div className="mb-3 flex items-center justify-between text-sm">
                            <div>
                                <span className="font-medium">
                                    {msg.from_name || msg.from_address}
                                </span>
                                {msg.from_name && (
                                    <span className="ml-1 text-muted-foreground">
                                        &lt;{msg.from_address}&gt;
                                    </span>
                                )}
                            </div>
                            <div className="flex items-center gap-2">
                                <Badge
                                    variant={
                                        msg.direction === 'inbound'
                                            ? 'secondary'
                                            : 'outline'
                                    }
                                >
                                    {msg.direction === 'inbound'
                                        ? 'Received'
                                        : 'Sent'}
                                </Badge>
                                <time className="text-muted-foreground">
                                    {new Date(
                                        msg.received_at,
                                    ).toLocaleString()}
                                </time>
                            </div>
                        </div>
                        <div className="text-sm text-muted-foreground">
                            To:{' '}
                            {msg.to_addresses?.join(', ') || 'Unknown'}
                            {msg.cc_addresses &&
                                msg.cc_addresses.length > 0 && (
                                    <span>
                                        {' '}
                                        | Cc:{' '}
                                        {msg.cc_addresses.join(', ')}
                                    </span>
                                )}
                        </div>
                        <div className="mt-3 border-t pt-3">
                            {(msg.resolved_body_html || msg.body_html) ? (
                                <iframe
                                    srcDoc={`<!DOCTYPE html><html><head><base href="${window.location.origin}/"><meta charset="utf-8"><style>body{font-family:system-ui,sans-serif;font-size:14px;color:#333;margin:0;padding:0;}a{color:#2563eb;}img{max-width:100%;height:auto;}</style></head><body>${msg.resolved_body_html || msg.body_html}</body></html>`}
                                    className="w-full border-0"
                                    sandbox="allow-same-origin"
                                    title="Email content"
                                    onLoad={(e) => {
                                        const iframe =
                                            e.target as HTMLIFrameElement;
                                        if (iframe.contentDocument?.body) {
                                            iframe.style.height =
                                                iframe.contentDocument.body
                                                    .scrollHeight +
                                                20 +
                                                'px';
                                        }
                                    }}
                                />
                            ) : (
                                <pre className="whitespace-pre-wrap text-sm">
                                    {msg.body_text || '(empty)'}
                                </pre>
                            )}
                        </div>
                        {msg.attachments && msg.attachments.length > 0 && (
                            <div className="mt-3 border-t pt-3">
                                <p className="mb-1 text-xs font-medium text-muted-foreground">
                                    Attachments
                                </p>
                                <div className="flex flex-wrap gap-2">
                                    {msg.attachments.map((att) => (
                                        <Badge
                                            key={att.id}
                                            variant="outline"
                                        >
                                            {att.filename} (
                                            {formatBytes(att.size)})
                                        </Badge>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                ))}
            </div>

            {/* Reply box */}
            <div className="border-t px-6 py-4">
                <form onSubmit={handleReply} className="space-y-3">
                    <textarea
                        placeholder="Write a reply..."
                        value={replyForm.data.body}
                        onChange={(e) =>
                            replyForm.setData('body', e.target.value)
                        }
                        rows={3}
                        className="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                    />
                    <div className="flex justify-end">
                        <Button
                            type="submit"
                            disabled={
                                replyForm.processing || !replyForm.data.body
                            }
                            size="sm"
                        >
                            <Send className="mr-1 size-4" />
                            Send reply
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    );
}

function ComposeDialog({
    customerOptions,
}: {
    customerOptions: CustomerOption[];
}) {
    const [open, setOpen] = useState(false);
    const form = useForm({
        to: '',
        subject: '',
        body: '',
        customer_id: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        form.post(store.url(), {
            onSuccess: () => {
                form.reset();
                setOpen(false);
            },
        });
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button size="sm">
                    <Pencil className="mr-1 size-4" />
                    Compose
                </Button>
            </DialogTrigger>
            <DialogContent className="max-w-lg">
                <DialogHeader>
                    <DialogTitle>New email</DialogTitle>
                </DialogHeader>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="grid gap-2">
                        <Label htmlFor="compose-to">To</Label>
                        <Input
                            id="compose-to"
                            type="email"
                            placeholder="recipient@example.com"
                            value={form.data.to}
                            onChange={(e) => form.setData('to', e.target.value)}
                            required
                        />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="compose-subject">Subject</Label>
                        <Input
                            id="compose-subject"
                            placeholder="Subject"
                            value={form.data.subject}
                            onChange={(e) =>
                                form.setData('subject', e.target.value)
                            }
                            required
                        />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="compose-customer">
                            Link to customer (optional)
                        </Label>
                        <Select
                            value={form.data.customer_id}
                            onValueChange={(v) =>
                                form.setData('customer_id', v)
                            }
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="None" />
                            </SelectTrigger>
                            <SelectContent>
                                {customerOptions.map((c) => (
                                    <SelectItem
                                        key={c.id}
                                        value={c.id.toString()}
                                    >
                                        {c.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="compose-body">Message</Label>
                        <textarea
                            id="compose-body"
                            placeholder="Write your message..."
                            value={form.data.body}
                            onChange={(e) =>
                                form.setData('body', e.target.value)
                            }
                            rows={6}
                            required
                            className="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                        />
                    </div>
                    <div className="flex justify-end">
                        <Button
                            type="submit"
                            disabled={form.processing}
                        >
                            <Send className="mr-1 size-4" />
                            Send
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function formatDate(date: string): string {
    const d = new Date(date);
    const now = new Date();
    const diff = now.getTime() - d.getTime();
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));

    if (days === 0) {
        return d.toLocaleTimeString(undefined, {
            hour: 'numeric',
            minute: '2-digit',
        });
    }

    if (days < 7) {
        return d.toLocaleDateString(undefined, { weekday: 'short' });
    }

    return d.toLocaleDateString(undefined, {
        month: 'short',
        day: 'numeric',
    });
}

function formatBytes(bytes: number): string {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}
