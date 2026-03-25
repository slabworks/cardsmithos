import { Head, Link, router } from '@inertiajs/react';
import { Form } from '@inertiajs/react';
import {
    Lock,
    MailOpen,
    Send,
    UserPlus,
} from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import echo from '@/echo';
import AppLayout from '@/layouts/app-layout';
import { index, linkCustomer, show, update } from '@/routes/conversations';
import { store as storeMessage } from '@/routes/conversations/messages';
import type { BreadcrumbItem } from '@/types';

type Message = {
    id: number;
    sender_type: string;
    body: string;
    read_at: string | null;
    created_at: string;
};

type Conversation = {
    id: number;
    subject: string | null;
    status: string;
    guest_name: string | null;
    guest_email: string;
    customer: { id: number; name: string } | null;
    customer_id: number | null;
    participant_name: string;
    participant_email: string;
    access_token: string;
    messages: Message[];
};

type CustomerOption = { id: number; name: string; email: string };

function formatTime(dateString: string): string {
    const date = new Date(dateString);
    return date.toLocaleString(undefined, {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
}

export default function ConversationsShow({
    conversation,
    customerOptions,
}: {
    conversation: Conversation;
    customerOptions: CustomerOption[];
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Conversations', href: index() },
        {
            title: conversation.subject || conversation.participant_name,
            href: show.url(conversation),
        },
    ];

    const messagesEndRef = useRef<HTMLDivElement>(null);
    const [messages, setMessages] = useState<Message[]>(conversation.messages);
    const [showLinkCustomer, setShowLinkCustomer] = useState(false);
    const [customerSearch, setCustomerSearch] = useState('');
    const [selectedCustomerId, setSelectedCustomerId] = useState<number | ''>('');
    const [showDropdown, setShowDropdown] = useState(false);
    const [createCustomer, setCreateCustomer] = useState(false);

    const filteredCustomers = useMemo(() => {
        if (!customerSearch) return customerOptions;
        const q = customerSearch.toLowerCase();
        return customerOptions.filter(
            (c) =>
                c.name.toLowerCase().includes(q) ||
                c.email.toLowerCase().includes(q),
        );
    }, [customerOptions, customerSearch]);

    const selectedCustomer = customerOptions.find(
        (c) => c.id === selectedCustomerId,
    );

    useEffect(() => {
        setMessages(conversation.messages);
    }, [conversation.messages]);

    useEffect(() => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, [messages]);

    useEffect(() => {
        const channel = echo.private(`conversation.${conversation.id}`);

        channel.listen('.App\\Events\\MessageSent', (event: Message) => {
            setMessages((prev) => {
                if (prev.some((m) => m.id === event.id)) return prev;
                return [...prev, event];
            });
        });

        return () => {
            echo.leave(`conversation.${conversation.id}`);
        };
    }, [conversation.id]);

    const handleStatusUpdate = (newStatus: string) => {
        router.put(update.url(conversation), { status: newStatus });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head
                title={
                    conversation.subject || conversation.participant_name
                }
            />
            <div className="flex h-full flex-1 flex-col overflow-hidden rounded-xl">
                {/* Header */}
                <div className="flex items-center justify-between border-b border-sidebar-border px-4 py-3">
                    <div className="flex flex-col gap-0.5">
                        <div className="flex items-center gap-2">
                            <h1 className="text-lg font-semibold">
                                {conversation.participant_name}
                            </h1>
                            <Badge variant="secondary">
                                {conversation.status}
                            </Badge>
                        </div>
                        <p className="text-sm text-muted-foreground">
                            {conversation.participant_email}
                            {conversation.customer && (
                                <>
                                    {' '}
                                    &middot;{' '}
                                    <Link
                                        href={`/customers/${conversation.customer.id}`}
                                        className="text-primary underline-offset-4 hover:underline"
                                    >
                                        {conversation.customer.name}
                                    </Link>
                                </>
                            )}
                        </p>
                        {conversation.subject && (
                            <p className="text-sm font-medium">
                                {conversation.subject}
                            </p>
                        )}
                    </div>
                    <div className="flex items-center gap-2">
                        {conversation.status === 'open' ? (
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={() => handleStatusUpdate('closed')}
                            >
                                <Lock className="mr-2 size-4" />
                                Close
                            </Button>
                        ) : (
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={() => handleStatusUpdate('open')}
                            >
                                <MailOpen className="mr-2 size-4" />
                                Reopen
                            </Button>
                        )}
                    </div>
                </div>

                {/* Link customer section */}
                {!conversation.customer && (
                    <div className="border-b border-sidebar-border bg-muted/30 px-4 py-2">
                        {!showLinkCustomer ? (
                            <Button
                                variant="ghost"
                                size="sm"
                                onClick={() => setShowLinkCustomer(true)}
                            >
                                <UserPlus className="mr-2 size-4" />
                                Link to customer
                            </Button>
                        ) : (
                            <Form
                                action={linkCustomer.url(conversation)}
                                method="post"
                                className="flex flex-wrap items-end gap-3"
                                onSuccess={() => setShowLinkCustomer(false)}
                            >
                                {({ errors, processing }) => (
                                    <>
                                        {!createCustomer && (
                                            <div className="grid min-w-[240px] gap-1">
                                                <Label htmlFor="link_customer_search">
                                                    Customer
                                                </Label>
                                                <input
                                                    type="hidden"
                                                    name="customer_id"
                                                    value={selectedCustomerId}
                                                />
                                                <div className="relative">
                                                    <Input
                                                        id="link_customer_search"
                                                        value={
                                                            selectedCustomer
                                                                ? selectedCustomer.name
                                                                : customerSearch
                                                        }
                                                        onChange={(e) => {
                                                            setCustomerSearch(e.target.value);
                                                            setSelectedCustomerId('');
                                                            setShowDropdown(true);
                                                        }}
                                                        onFocus={() => setShowDropdown(true)}
                                                        onBlur={() =>
                                                            setTimeout(
                                                                () => setShowDropdown(false),
                                                                200,
                                                            )
                                                        }
                                                        placeholder="Search customers..."
                                                        autoComplete="off"
                                                    />
                                                    {showDropdown &&
                                                        filteredCustomers.length > 0 &&
                                                        !selectedCustomer && (
                                                            <ul className="absolute z-10 mt-1 max-h-48 w-full overflow-auto rounded-md border border-input bg-popover shadow-md">
                                                                {filteredCustomers.map((c) => (
                                                                    <li key={c.id}>
                                                                        <button
                                                                            type="button"
                                                                            className="w-full px-3 py-2 text-left text-sm hover:bg-muted"
                                                                            onMouseDown={(e) => {
                                                                                e.preventDefault();
                                                                                setSelectedCustomerId(c.id);
                                                                                setCustomerSearch('');
                                                                                setShowDropdown(false);
                                                                            }}
                                                                        >
                                                                            <span className="font-medium">
                                                                                {c.name}
                                                                            </span>
                                                                            <span className="ml-2 text-muted-foreground">
                                                                                {c.email}
                                                                            </span>
                                                                        </button>
                                                                    </li>
                                                                ))}
                                                            </ul>
                                                        )}
                                                </div>
                                                {selectedCustomer && (
                                                    <button
                                                        type="button"
                                                        className="text-xs text-muted-foreground hover:text-foreground"
                                                        onClick={() => {
                                                            setSelectedCustomerId('');
                                                            setCustomerSearch('');
                                                        }}
                                                    >
                                                        Clear selection
                                                    </button>
                                                )}
                                                <InputError message={errors.customer_id} />
                                            </div>
                                        )}
                                        {!selectedCustomer && (
                                            <div className="flex items-center gap-2">
                                                <input
                                                    type="hidden"
                                                    name="create_customer"
                                                    value="0"
                                                />
                                                <input
                                                    id="link_create_customer"
                                                    name="create_customer"
                                                    type="checkbox"
                                                    value="1"
                                                    checked={createCustomer}
                                                    onChange={(e) => {
                                                        setCreateCustomer(e.target.checked);
                                                        if (e.target.checked) {
                                                            setSelectedCustomerId('');
                                                            setCustomerSearch('');
                                                        }
                                                    }}
                                                    className="size-4 rounded border-input"
                                                />
                                                <Label htmlFor="link_create_customer">
                                                    Create new customer
                                                </Label>
                                            </div>
                                        )}
                                        <div className="flex items-center gap-2">
                                            <Button
                                                type="submit"
                                                size="sm"
                                                disabled={processing}
                                            >
                                                Link
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => {
                                                    setShowLinkCustomer(false);
                                                    setSelectedCustomerId('');
                                                    setCustomerSearch('');
                                                    setCreateCustomer(false);
                                                }}
                                            >
                                                Cancel
                                            </Button>
                                        </div>
                                    </>
                                )}
                            </Form>
                        )}
                    </div>
                )}

                {/* Messages thread */}
                <div className="flex-1 overflow-y-auto p-4">
                    <div className="mx-auto flex max-w-3xl flex-col gap-3">
                        {messages.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-12 text-center">
                                <p className="text-sm text-muted-foreground">
                                    No messages yet. Send the first message below.
                                </p>
                            </div>
                        ) : (
                            messages.map((message) => {
                                const isOwner = message.sender_type === 'user';
                                return (
                                    <div
                                        key={message.id}
                                        className={`flex ${isOwner ? 'justify-end' : 'justify-start'}`}
                                    >
                                        <div
                                            className={`max-w-[75%] rounded-lg px-4 py-2 ${
                                                isOwner
                                                    ? 'bg-primary text-primary-foreground'
                                                    : 'bg-muted'
                                            }`}
                                        >
                                            <p className="whitespace-pre-wrap text-sm">
                                                {message.body}
                                            </p>
                                            <p
                                                className={`mt-1 text-xs ${
                                                    isOwner
                                                        ? 'text-primary-foreground/70'
                                                        : 'text-muted-foreground'
                                                }`}
                                            >
                                                {formatTime(message.created_at)}
                                            </p>
                                        </div>
                                    </div>
                                );
                            })
                        )}
                        <div ref={messagesEndRef} />
                    </div>
                </div>

                {/* Message input */}
                <div className="border-t border-sidebar-border p-4">
                    <Form
                        action={storeMessage.url(conversation)}
                        method="post"
                        className="mx-auto flex max-w-3xl items-start gap-2"
                    >
                        {({ errors, processing }) => (
                            <>
                                <div className="flex-1">
                                    <textarea
                                        name="body"
                                        rows={2}
                                        required
                                        placeholder="Type your message..."
                                        className="flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                    />
                                    <InputError message={errors.body} />
                                </div>
                                <Button type="submit" disabled={processing}>
                                    <Send className="mr-2 size-4" />
                                    Send
                                </Button>
                            </>
                        )}
                    </Form>
                </div>
            </div>
        </AppLayout>
    );
}
