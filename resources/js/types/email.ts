export type EmailItem = {
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
