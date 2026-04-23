import type { Auth } from '@/types/auth';

declare module '@inertiajs/core' {
        export interface InertiaConfig {
            sharedPageProps: {
                name: string;
                socials: {
                    github: string;
                    patreon: string;
                    discord: string;
                };
                auth: Auth;
                sidebarOpen: boolean;
                [key: string]: unknown;
        };
    }
}
