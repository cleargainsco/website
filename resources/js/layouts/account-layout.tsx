import { Link, router, usePage } from '@inertiajs/react';
import { LogOut } from 'lucide-react';
import type { ReactNode } from 'react';
import { Eyebrow } from '@/components/marketing/eyebrow';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn } from '@/lib/utils';
import { logout } from '@/routes';
import { index as accountIndex } from '@/routes/account';
import { index as ordersIndex } from '@/routes/account/orders';
import { edit as editProfile } from '@/routes/account/profile';

const accountLinks = [
    { title: 'Overview', href: accountIndex() },
    { title: 'Orders', href: ordersIndex() },
    { title: 'Profile', href: editProfile() },
];

export default function AccountLayout({ children }: { children: ReactNode }) {
    const { auth } = usePage().props;
    const { isCurrentUrl } = useCurrentUrl();

    return (
        <div className="mx-auto max-w-7xl px-6 py-14 lg:px-8 lg:py-20">
            <Eyebrow>My account</Eyebrow>
            <h1 className="mt-3 font-serif text-4xl font-medium text-deep-ocean">
                {auth.user.name}
            </h1>
            <p className="mt-1 text-sm text-charcoal/60">{auth.user.email}</p>

            <div className="mt-10 grid gap-10 lg:grid-cols-[14rem_1fr]">
                <nav
                    aria-label="Account"
                    className="flex gap-1 overflow-x-auto lg:flex-col lg:items-start"
                >
                    {accountLinks.map((link) => (
                        <Link
                            key={link.title}
                            href={link.href}
                            prefetch
                            className={cn(
                                'rounded-full px-4 py-2 text-sm font-medium whitespace-nowrap transition-colors',
                                isCurrentUrl(link.href)
                                    ? 'bg-deep-ocean text-linen'
                                    : 'text-charcoal/70 hover:bg-sand/60 hover:text-deep-ocean',
                            )}
                        >
                            {link.title}
                        </Link>
                    ))}

                    <div className="mx-2 my-3 hidden w-full border-t border-charcoal/10 lg:block" />

                    <Link
                        href={logout()}
                        as="button"
                        onClick={() => router.flushAll()}
                        className="inline-flex cursor-pointer items-center gap-2 rounded-full px-4 py-2 text-sm font-medium whitespace-nowrap text-charcoal/70 transition-colors hover:bg-sand/60 hover:text-deep-ocean"
                    >
                        <LogOut className="size-4" />
                        Log out
                    </Link>
                </nav>

                <div className="min-w-0">{children}</div>
            </div>
        </div>
    );
}
