import type { Auth } from '@/types/auth';
import type { WelcomeOfferCopy } from '@/types/marketing';

declare module 'react' {
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            sidebarOpen: boolean;
            supportEmail: string;
            cartCount: number;
            ageVerified: boolean;
            welcomeOffer: WelcomeOfferCopy | null;
            [key: string]: unknown;
        };
    }
}
