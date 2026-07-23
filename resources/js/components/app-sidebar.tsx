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
import { dashboard, home } from '@/routes';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { Link as LinkIcon, Folder, LayoutGrid, Search } from 'lucide-react';
import AppLogo from './app-logo';
import { useLang } from '@/hooks/useLang';

export function AppSidebar() {
    const { t } = useLang();

    const mainNavItems: NavItem[] = [
        {
            title: 'menu.dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: 'menu.search',
            href: '/search',
            icon: Search,
        },
    ];

    const footerNavItems: NavItem[] = [
        {
            title: 'menu.ludoCH',
            href: 'https://ludo.ch',
            icon: LinkIcon,
        },
        {
            title: 'menu.project',
            href: 'https://github.com/MeKHell/LudoTest',
            icon: Folder,
        },
    ];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={home()} prefetch>
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
