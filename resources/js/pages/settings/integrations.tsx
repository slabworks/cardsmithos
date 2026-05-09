import { Form, Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Settings', href: '/settings/profile' },
    { title: 'Integrations', href: '/settings/integrations' },
];

export default function Integrations({
    gmail,
}: {
    gmail: {
        configured: boolean;
        missingConfig: string[];
        connected: boolean;
        email: string | null;
        lastSyncedAt: string | null;
        scopes: string[];
    };
}) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Integrations" />
            <SettingsLayout>
                <div className="space-y-6">
                    <Heading
                        variant="small"
                        title="Integrations"
                        description="Connect external services used by CardsmithOS."
                    />

                    <div className="rounded-lg border border-sidebar-border bg-card p-4">
                        <div className="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <h2 className="font-medium">Gmail</h2>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    Sync inbox contacts and latest message
                                    previews without storing full email bodies.
                                </p>
                                {gmail.connected && (
                                    <p className="mt-2 text-sm">
                                        Connected as {gmail.email}
                                    </p>
                                )}
                                {!gmail.configured && (
                                    <p className="mt-2 text-sm text-destructive">
                                        Missing Gmail config:{' '}
                                        {gmail.missingConfig.join(', ')}.
                                    </p>
                                )}
                            </div>
                            {gmail.connected ? (
                                <Form
                                    action="/settings/integrations/gmail"
                                    method="delete"
                                >
                                    {({ processing }) => (
                                        <Button
                                            type="submit"
                                            variant="destructive"
                                            disabled={processing}
                                        >
                                            Disconnect
                                        </Button>
                                    )}
                                </Form>
                            ) : (
                                <>
                                    {gmail.configured ? (
                                        <Button asChild>
                                            <a href="/settings/integrations/gmail/redirect">
                                                Connect Gmail
                                            </a>
                                        </Button>
                                    ) : (
                                        <Button disabled>Connect Gmail</Button>
                                    )}
                                </>
                            )}
                        </div>
                    </div>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
