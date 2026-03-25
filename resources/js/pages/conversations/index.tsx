import { Head, Link, usePoll } from '@inertiajs/react';
import { MessageSquare, Plus, Search } from 'lucide-react';
import { useMemo, useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { create, index, show } from '@/routes/conversations';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Conversations', href: index() },
];

type ConversationItem = {
    id: number;
    subject: string | null;
    status: string;
    guest_name: string | null;
    guest_email: string;
    last_message_at: string | null;
    customer: { id: number; name: string } | null;
    latest_message: {
        id: number;
        body: string;
        sender_type: string;
        created_at: string;
    } | null;
    participant_name: string;
    unread_count: number;
};

type StatusOption = { value: string; label: string; color: string };

function timeAgo(dateString: string): string {
    const now = new Date();
    const date = new Date(dateString);
    const seconds = Math.floor((now.getTime() - date.getTime()) / 1000);

    if (seconds < 60) return 'just now';
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes}m ago`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours}h ago`;
    const days = Math.floor(hours / 24);
    if (days < 30) return `${days}d ago`;
    return date.toLocaleDateString(undefined, { dateStyle: 'medium' });
}

function statusBadgeVariant(
    status: string,
    statusOptions: StatusOption[],
): string {
    const option = statusOptions.find((o) => o.value === status);
    return option?.color ?? 'secondary';
}

export default function ConversationsIndex({
    conversations,
    statusOptions,
}: {
    conversations: ConversationItem[];
    statusOptions: StatusOption[];
}) {
    usePoll(15000);

    const [search, setSearch] = useState('');

    const filtered = useMemo(() => {
        const q = search.toLowerCase();

        if (!q) {
            return conversations;
        }

        return conversations.filter(
            (c) =>
                c.participant_name.toLowerCase().includes(q) ||
                c.guest_email.toLowerCase().includes(q) ||
                c.subject?.toLowerCase().includes(q) ||
                c.latest_message?.body.toLowerCase().includes(q) ||
                c.customer?.name.toLowerCase().includes(q),
        );
    }, [conversations, search]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Conversations" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-xl font-semibold">Conversations</h1>
                        {conversations.length > 0 && (
                            <p className="text-sm text-muted-foreground">
                                {conversations.filter((c) => c.unread_count > 0).length}{' '}
                                unread
                            </p>
                        )}
                    </div>
                    <Button asChild>
                        <Link href={create()}>
                            <Plus className="mr-2 size-4" />
                            New conversation
                        </Link>
                    </Button>
                </div>
                {conversations.length > 0 && (
                    <div className="relative">
                        <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            placeholder="Search conversations..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            className="pl-9"
                        />
                    </div>
                )}
                <div className="rounded-lg border border-sidebar-border bg-card">
                    {conversations.length === 0 ? (
                        <div className="flex flex-col items-center justify-center gap-2 py-12 text-center">
                            <MessageSquare className="size-10 text-muted-foreground" />
                            <p className="text-sm text-muted-foreground">
                                No conversations yet
                            </p>
                            <Button asChild variant="outline">
                                <Link href={create()}>
                                    Start your first conversation
                                </Link>
                            </Button>
                        </div>
                    ) : filtered.length === 0 ? (
                        <div className="flex flex-col items-center justify-center gap-2 py-12 text-center">
                            <p className="text-sm text-muted-foreground">
                                No conversations match your search
                            </p>
                        </div>
                    ) : (
                        <ul className="divide-y divide-sidebar-border">
                            {filtered.map((conversation) => (
                                <li key={conversation.id}>
                                    <Link
                                        href={show.url(conversation)}
                                        className="flex items-center justify-between px-4 py-3 hover:bg-muted/50"
                                    >
                                        <div className="flex min-w-0 flex-col gap-0.5">
                                            <div className="flex items-center gap-2">
                                                <span
                                                    className={`font-medium ${conversation.unread_count > 0 ? 'text-foreground' : ''}`}
                                                >
                                                    {conversation.participant_name}
                                                </span>
                                                {conversation.unread_count > 0 && (
                                                    <Badge variant="default" className="text-xs">
                                                        {conversation.unread_count}
                                                    </Badge>
                                                )}
                                            </div>
                                            {conversation.subject && (
                                                <span className="text-sm font-medium text-foreground">
                                                    {conversation.subject}
                                                </span>
                                            )}
                                            {conversation.latest_message && (
                                                <span className="truncate text-sm text-muted-foreground">
                                                    {conversation.latest_message.body.length > 80
                                                        ? conversation.latest_message.body.slice(0, 80) + '...'
                                                        : conversation.latest_message.body}
                                                </span>
                                            )}
                                        </div>
                                        <div className="flex shrink-0 items-center gap-3">
                                            {conversation.last_message_at && (
                                                <span className="text-xs text-muted-foreground">
                                                    {timeAgo(conversation.last_message_at)}
                                                </span>
                                            )}
                                            <Badge
                                                variant={
                                                    statusBadgeVariant(
                                                        conversation.status,
                                                        statusOptions,
                                                    ) as 'default' | 'secondary' | 'destructive' | 'outline'
                                                }
                                            >
                                                {statusOptions.find(
                                                    (o) => o.value === conversation.status,
                                                )?.label ?? conversation.status}
                                            </Badge>
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
