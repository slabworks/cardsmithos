import { Form, Head, Link } from '@inertiajs/react';
import { ArrowLeft, Lock, Send } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import echo from '@/echo';
import { Button } from '@/components/ui/button';
import InputError from '@/components/input-error';
import { Spinner } from '@/components/ui/spinner';

type Message = {
    id: number;
    sender_type: string;
    body: string;
    read_at: string | null;
    created_at: string;
};

type Props = {
    slug: string;
    accessToken: string;
    companyName: string;
    conversation: {
        id: number;
        subject: string | null;
        status: string;
        messages: Message[];
    };
};

function formatTimestamp(dateString: string): string {
    const date = new Date(dateString);
    const now = new Date();
    const isToday = date.toDateString() === now.toDateString();

    if (isToday) {
        return date.toLocaleTimeString([], {
            hour: 'numeric',
            minute: '2-digit',
        });
    }

    return date.toLocaleDateString([], {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
}

export default function ShowPublicMessage({
    slug,
    accessToken,
    companyName,
    conversation,
}: Props) {
    const [messages, setMessages] = useState<Message[]>(
        conversation.messages,
    );
    const messagesEndRef = useRef<HTMLDivElement>(null);
    const isOpen = conversation.status === 'open';

    useEffect(() => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, [messages]);

    useEffect(() => {
        const channel = echo.channel(`conversation.${accessToken}`);
        channel.listen(
            '.App\\Events\\MessageSent',
            (data: Message) => {
                setMessages((prev) => {
                    if (prev.some((m) => m.id === data.id)) {
                        return prev;
                    }
                    return [...prev, data];
                });
            },
        );

        return () => {
            echo.leave(`conversation.${accessToken}`);
        };
    }, [accessToken]);

    return (
        <>
            <Head
                title={`Conversation with ${companyName}`}
            />
            <div className="flex h-screen flex-col bg-[#f5f5f4]">
                <header className="border-b border-[#e8e8e6] bg-white px-4 py-3">
                    <div className="mx-auto flex max-w-2xl items-center gap-3">
                        <Link
                            href={`/c/${slug}`}
                            className="text-[#575754] hover:text-[#1b1b18]"
                        >
                            <ArrowLeft className="h-5 w-5" />
                        </Link>
                        <div>
                            <h1 className="text-sm font-semibold text-[#1b1b18]">
                                {companyName}
                            </h1>
                            {conversation.subject && (
                                <p className="text-xs text-[#575754]">
                                    {conversation.subject}
                                </p>
                            )}
                        </div>
                    </div>
                </header>

                <main className="flex-1 overflow-y-auto px-4 py-6">
                    <div className="mx-auto max-w-2xl space-y-4">
                        {messages.map((message) => {
                            const isCustomer =
                                message.sender_type === 'customer';
                            return (
                                <div
                                    key={message.id}
                                    className={`flex ${isCustomer ? 'justify-end' : 'justify-start'}`}
                                >
                                    <div
                                        className={`max-w-[75%] rounded-lg px-4 py-2.5 ${
                                            isCustomer
                                                ? 'bg-blue-600 text-white'
                                                : 'border border-[#e8e8e6] bg-white text-[#1b1b18]'
                                        }`}
                                    >
                                        <p className="whitespace-pre-wrap text-sm">
                                            {message.body}
                                        </p>
                                        <p
                                            className={`mt-1 text-xs ${
                                                isCustomer
                                                    ? 'text-blue-200'
                                                    : 'text-[#a1a19a]'
                                            }`}
                                        >
                                            {formatTimestamp(
                                                message.created_at,
                                            )}
                                        </p>
                                    </div>
                                </div>
                            );
                        })}
                        <div ref={messagesEndRef} />
                    </div>
                </main>

                {isOpen ? (
                    <footer className="border-t border-[#e8e8e6] bg-white px-4 py-3">
                        <div className="mx-auto max-w-2xl">
                            <Form
                                action={`/c/${slug}/messages/${accessToken}`}
                                method="post"
                                onSuccess={() => {
                                    const textarea =
                                        document.getElementById(
                                            'body',
                                        ) as HTMLTextAreaElement | null;
                                    if (textarea) {
                                        textarea.value = '';
                                    }
                                }}
                                className="flex items-end gap-2"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="flex-1">
                                            <textarea
                                                id="body"
                                                name="body"
                                                rows={1}
                                                required
                                                placeholder="Type a message..."
                                                className="flex w-full resize-none rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                                onKeyDown={(e) => {
                                                    if (
                                                        e.key === 'Enter' &&
                                                        !e.shiftKey
                                                    ) {
                                                        e.preventDefault();
                                                        const form =
                                                            e.currentTarget.closest(
                                                                'form',
                                                            );
                                                        if (form) {
                                                            form.requestSubmit();
                                                        }
                                                    }
                                                }}
                                            />
                                            <InputError
                                                message={errors.body}
                                            />
                                        </div>
                                        <Button
                                            type="submit"
                                            size="icon"
                                            disabled={processing}
                                        >
                                            {processing ? (
                                                <Spinner />
                                            ) : (
                                                <Send className="h-4 w-4" />
                                            )}
                                        </Button>
                                    </>
                                )}
                            </Form>
                        </div>
                    </footer>
                ) : (
                    <footer className="border-t border-[#e8e8e6] bg-white px-4 py-4">
                        <div className="mx-auto flex max-w-2xl items-center justify-center gap-2 text-sm text-[#a1a19a]">
                            <Lock className="h-4 w-4" />
                            This conversation has been closed.
                        </div>
                    </footer>
                )}
            </div>
        </>
    );
}
