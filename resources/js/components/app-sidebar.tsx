import { Link, usePage } from '@inertiajs/react';
import {
    Github,
    Heart,
    LayoutGrid,
    Mail,
    MessageCircle,
    MessageSquare,
    Receipt,
    Users,
} from 'lucide-react';
import { useMemo } from 'react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as customersIndex } from '@/routes/customers';
import { index as emailsIndex } from '@/routes/emails';
import { index as expensesIndex } from '@/routes/expenses';
import { index as inquiriesIndex } from '@/routes/inquiries';
import type { NavItem } from '@/types';

const footerNavItems: NavItem[] = [
    {
        title: 'GitHub',
        href: 'https://github.com/slabworks/cardsmithos',
        icon: Github,
    },
    {
        title: 'Patreon',
        href: 'https://www.patreon.com/c/CardSmithOS',
        icon: Heart,
    },
    {
        title: 'Discord',
        href: 'https://discord.gg/ycBacKEyhW',
        icon: MessageCircle,
    },
];

export function AppSidebar() {
    const { unreadEmailCount } = usePage<{ unreadEmailCount: number }>().props;

    const mainNavItems: NavItem[] = useMemo(
        () => [
            { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
            { title: 'Customers', href: customersIndex(), icon: Users },
            {
                title: 'Email',
                href: emailsIndex(),
                icon: Mail,
                badge: unreadEmailCount,
            },
            { title: 'Inquiries', href: inquiriesIndex(), icon: MessageSquare },
            { title: 'Expenses', href: expensesIndex(), icon: Receipt },
        ],
        [unreadEmailCount],
    );

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
