import { Head, router } from '@inertiajs/react';
import { RefreshCw } from 'lucide-react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { sync } from '@/routes/emails';
import { connect, disconnect, edit } from '@/routes/gmail';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Gmail',
        href: edit(),
    },
];

export default function Gmail({
    gmailAccount,
}: {
    gmailAccount: {
        email: string;
        last_synced_at: string | null;
    } | null;
}) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Gmail" />

            <h1 className="sr-only">Gmail integration</h1>

            <SettingsLayout>
                <div className="space-y-6">
                    <Heading
                        variant="small"
                        title="Gmail"
                        description="Connect your Gmail account to view and send emails from within Cardsmith OS"
                    />

                    {gmailAccount ? (
                        <div className="space-y-4">
                            <div className="rounded-lg border p-4">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <p className="text-sm font-medium">
                                            Connected account
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            {gmailAccount.email}
                                        </p>
                                        {gmailAccount.last_synced_at && (
                                            <p className="mt-1 text-xs text-muted-foreground">
                                                Last synced:{' '}
                                                {new Date(
                                                    gmailAccount.last_synced_at,
                                                ).toLocaleString()}
                                            </p>
                                        )}
                                    </div>
                                    <div className="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                                        <div className="h-2 w-2 rounded-full bg-green-600 dark:bg-green-400" />
                                    </div>
                                </div>
                            </div>

                            <div className="flex gap-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => router.post(sync.url())}
                                >
                                    <RefreshCw className="mr-1 size-4" />
                                    Sync now
                                </Button>
                            </div>

                            <Button
                                variant="destructive"
                                size="sm"
                                onClick={() => {
                                    if (
                                        confirm(
                                            'Are you sure you want to disconnect your Gmail account? All synced emails will be removed.',
                                        )
                                    ) {
                                        router.post(disconnect.url());
                                    }
                                }}
                            >
                                Disconnect Gmail
                            </Button>
                        </div>
                    ) : (
                        <div className="space-y-4">
                            <div className="rounded-lg border border-dashed p-6 text-center">
                                <p className="text-sm text-muted-foreground">
                                    No Gmail account connected. Connect your
                                    account to sync emails and manage
                                    communications from within Cardsmith OS.
                                </p>
                            </div>

                            <Button asChild>
                                <a href={connect.url()}>Connect Gmail</a>
                            </Button>
                        </div>
                    )}
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
