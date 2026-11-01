import type { ReactNode } from 'react';
import { AgeGate } from '@/components/marketing/age-gate';
import { SiteFooter } from '@/components/marketing/site-footer';
import { SiteHeader } from '@/components/marketing/site-header';
import { WelcomeOffer } from '@/components/marketing/welcome-offer';

export default function MarketingLayout({ children }: { children: ReactNode }) {
    return (
        <div className="flex min-h-screen flex-col bg-linen font-sans text-charcoal">
            <p className="bg-charcoal px-6 py-2 text-center text-[0.7rem] tracking-[0.2em] text-linen/80 uppercase">
                For research use only · Not for human consumption
            </p>
            <SiteHeader />
            <main className="flex-1">{children}</main>
            <SiteFooter />
            <AgeGate />
            <WelcomeOffer />
        </div>
    );
}
