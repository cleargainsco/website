import { createInertiaApp, router } from '@inertiajs/react';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import AccountLayout from '@/layouts/account-layout';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';
import MarketingLayout from '@/layouts/marketing-layout';
import SettingsLayout from '@/layouts/settings/layout';

const storefrontPagePrefixes = ['marketing/', 'account/', 'auth/'];

const usesAdminTheme = (name: string): boolean =>
    !storefrontPagePrefixes.some((prefix) => name.startsWith(prefix));

void createInertiaApp({
    serverHead: true,
    layout: (name) => {
        switch (true) {
            case name.startsWith('marketing/'):
                return MarketingLayout;
            case name.startsWith('account/'):
                return [MarketingLayout, AccountLayout];
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    strictMode: true,
    withApp(app) {
        return (
            <TooltipProvider delayDuration={0}>
                {app}
                <Toaster />
            </TooltipProvider>
        );
    },
    progress: {
        color: '#4B5563',
    },
});

if (typeof window !== 'undefined') {
    router.on('navigate', (event) => {
        document.documentElement.classList.toggle(
            'theme-admin',
            usesAdminTheme(event.detail.page.component),
        );
    });
}
