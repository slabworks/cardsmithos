import { Head } from '@inertiajs/react';

type Activity = {
    id: number;
    type: string;
    title: string;
    description: string | null;
    occurred_at: string;
};

type Photo = {
    id: number;
    url: string;
    name: string;
};

export default function CardsTimelinePublic({
    card,
    photos = [],
}: {
    card: {
        id: number;
        name: string;
        customer: { id: number; name: string };
        activities: Activity[];
    };
    photos?: Photo[];
}) {
    const formatOccurredAt = (iso: string) => {
        const d = new Date(iso);

        return d.toLocaleString(undefined, {
            dateStyle: 'medium',
            timeStyle: 'short',
        });
    };

    const typeLabel = (type: string) =>
        type === 'milestone' ? 'Milestone' : 'Activity';

    return (
        <>
            <Head title={`Timeline: ${card.name}`} />
            <div className="min-h-svh bg-background p-4 md:p-8">
                <div className="mx-auto max-w-2xl">
                    <div className="rounded-lg border border-sidebar-border bg-card shadow-sm">
                        <div className="border-b border-sidebar-border px-4 py-4 md:px-6">
                            <h1 className="text-lg font-semibold">
                                {card.name}
                            </h1>
                            {card.customer?.name && (
                                <p className="mt-1 text-sm text-muted-foreground">
                                    {card.customer.name}
                                </p>
                            )}
                        </div>
                        {photos.length > 0 && (
                            <div className="border-b border-sidebar-border px-4 py-4 md:px-6">
                                <h2 className="mb-3 text-sm font-medium text-muted-foreground">
                                    Photos
                                </h2>
                                <div className="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                    {photos.map((photo) => (
                                        <img
                                            key={photo.id}
                                            src={photo.url}
                                            alt={photo.name}
                                            className="aspect-square w-full rounded-lg object-cover"
                                        />
                                    ))}
                                </div>
                            </div>
                        )}
                        <div className="px-4 py-4 md:px-6">
                            {card.activities.length === 0 &&
                            photos.length === 0 ? (
                                <p className="py-8 text-center text-sm text-muted-foreground">
                                    No timeline entries yet.
                                </p>
                            ) : (
                                <ul className="space-y-6">
                                    {card.activities.map((activity) => (
                                        <li
                                            key={activity.id}
                                            className="border-l-2 border-sidebar-border pl-4"
                                        >
                                            <div className="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                                                <span className="font-medium uppercase">
                                                    {typeLabel(activity.type)}
                                                </span>
                                                <span>
                                                    {formatOccurredAt(
                                                        activity.occurred_at,
                                                    )}
                                                </span>
                                            </div>
                                            <p className="mt-1 font-medium">
                                                {activity.title}
                                            </p>
                                            {activity.description && (
                                                <p className="mt-1 text-sm text-muted-foreground">
                                                    {activity.description}
                                                </p>
                                            )}
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
