import { Link, usePage } from '@inertiajs/react';
import { BookOpen, LayoutGrid, ShieldCheck, Tag } from 'lucide-react';
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
import { dashboard, pricing } from '@/routes';
import { index as coursesIndex } from '@/routes/courses';
import { dashboard as adminDashboard } from '@/routes/filament/admin/pages';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Course',
        href: coursesIndex(),
        icon: BookOpen,
    },
    {
        title: 'Paket',
        href: pricing(),
        icon: Tag,
    },
];

const adminNavItems: NavItem[] = [
    {
        title: 'Admin panel',
        href: adminDashboard(),
        icon: ShieldCheck,
    },
];

export function AppSidebar() {
    const { auth } = usePage().props;

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
                {auth.user.is_admin && (
                    <NavFooter items={adminNavItems} className="mt-auto" />
                )}
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
