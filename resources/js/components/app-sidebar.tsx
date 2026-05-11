import { Link, usePage } from '@inertiajs/react';
import {
    ClipboardList,
    Github,
    Heart,
    LayoutGrid,
    MessageCircle,
    Receipt,
    TrendingUp,
    Users,
} from 'lucide-react';
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
import { index as expensesIndex } from '@/routes/expenses';
import { index as submissionsIndex } from '@/routes/submissions';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Submissions',
        href: submissionsIndex(),
        icon: ClipboardList,
    },
    {
        title: 'Customers',
        href: customersIndex(),
        icon: Users,
    },
    {
        title: 'Expenses',
        href: expensesIndex(),
        icon: Receipt,
    },
    {
        title: 'Statistics',
        href: '/statistics',
        icon: TrendingUp,
    },
];

export function AppSidebar() {
    const { socials } = usePage().props;
    const footerNavItems: NavItem[] = [
        {
            title: 'GitHub',
            href: socials.github,
            icon: Github,
        },
        {
            title: 'Patreon',
            href: socials.patreon,
            icon: Heart,
        },
        {
            title: 'Discord',
            href: socials.discord,
            icon: MessageCircle,
        },
    ];

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
