import { Link } from '@inertiajs/react';
import {
    AtSign,
    FileText,
    Gift,
    LayoutGrid,
    Mail,
    Package,
    ReceiptText,
    Search,
    Store,
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
import { dashboard, home } from '@/routes';
import { index as customersIndex } from '@/routes/admin/customers';
import { index as emailsIndex } from '@/routes/admin/emails';
import { index as ordersIndex } from '@/routes/admin/orders';
import { edit as homePageEdit } from '@/routes/admin/pages/home';
import { index as productsIndex } from '@/routes/admin/products';
import { index as seoIndex } from '@/routes/admin/seo';
import { index as subscribersIndex } from '@/routes/admin/subscribers';
import { edit as welcomeOfferEdit } from '@/routes/admin/welcome-offer';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Orders',
        href: ordersIndex(),
        icon: ReceiptText,
    },
    {
        title: 'Products',
        href: productsIndex(),
        icon: Package,
    },
    {
        title: 'Customers',
        href: customersIndex(),
        icon: Users,
    },
    {
        title: 'Pages',
        href: homePageEdit(),
        icon: FileText,
        items: [{ title: 'Home', href: homePageEdit() }],
    },
    {
        title: 'Welcome offer',
        href: welcomeOfferEdit(),
        icon: Gift,
    },
    {
        title: 'Subscribers',
        href: subscribersIndex(),
        icon: AtSign,
    },
    {
        title: 'Emails',
        href: emailsIndex(),
        icon: Mail,
    },
    {
        title: 'SEO',
        href: seoIndex(),
        icon: Search,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'View shop',
        href: home(),
        icon: Store,
    },
];

export function AppSidebar() {
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
