import { Link, usePage } from '@inertiajs/react';
import { Menu, UserRound } from 'lucide-react';
import {
    Sheet,
    SheetContent,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { brandButton } from '@/lib/brand-button';
import { primaryLinks } from '@/lib/storefront-links';
import { cn } from '@/lib/utils';
import { dashboard, home, login, register } from '@/routes';
import { index as shop } from '@/routes/shop';

export function SiteHeader() {
    const { isCurrentOrParentUrl } = useCurrentUrl();
    const { auth } = usePage().props;

    return (
        <header className="sticky top-0 z-40 border-b border-charcoal/10 bg-linen/90 backdrop-blur-md">
            <div className="mx-auto flex h-20 max-w-7xl items-center justify-between gap-6 px-6 lg:px-8">
                <Link href={home()} className="shrink-0">
                    <img
                        src="/images/brand/clear-gains-horizontal.svg"
                        alt="Clear Gains — Research Peptides"
                        className="h-10 w-auto sm:h-11"
                    />
                </Link>

                <nav className="hidden items-center gap-6 lg:flex xl:gap-8">
                    {primaryLinks.map((link) => (
                        <Link
                            key={link.title}
                            href={link.href}
                            className={cn(
                                'text-sm font-medium text-charcoal/70 transition-colors hover:text-deep-ocean',
                                isCurrentOrParentUrl(link.href) &&
                                    'text-deep-ocean',
                            )}
                        >
                            {link.title}
                        </Link>
                    ))}
                </nav>

                <div className="hidden items-center gap-5 lg:flex">
                    {auth.user ? (
                        <Link
                            href={dashboard()}
                            className="inline-flex items-center gap-2 text-sm font-medium text-charcoal/70 hover:text-deep-ocean"
                        >
                            <UserRound className="size-4" />
                            My account
                        </Link>
                    ) : (
                        <>
                            <Link
                                href={login()}
                                className="text-sm font-medium text-charcoal/70 hover:text-deep-ocean"
                            >
                                Log in
                            </Link>
                            <Link
                                href={register()}
                                className={brandButton({
                                    variant: 'secondary',
                                    size: 'sm',
                                })}
                            >
                                Register
                            </Link>
                        </>
                    )}
                    <Link
                        href={shop()}
                        className={cn(
                            brandButton({ size: 'sm' }),
                            'hidden xl:inline-flex',
                        )}
                    >
                        Shop peptides
                    </Link>
                </div>

                <Sheet>
                    <SheetTrigger
                        className="rounded-full p-2 text-charcoal hover:bg-sand lg:hidden"
                        aria-label="Open menu"
                    >
                        <Menu className="size-6" />
                    </SheetTrigger>
                    <SheetContent
                        side="right"
                        className="w-72 border-charcoal/10 bg-linen p-8 text-charcoal"
                    >
                        <SheetTitle className="sr-only">Menu</SheetTitle>
                        <img
                            src="/images/brand/clear-gains-wordmark.svg"
                            alt="Clear Gains"
                            className="w-36"
                        />
                        <nav className="mt-10 flex flex-col gap-6">
                            {primaryLinks.map((link) => (
                                <Link
                                    key={link.title}
                                    href={link.href}
                                    className="font-serif text-2xl text-charcoal hover:text-deep-ocean"
                                >
                                    {link.title}
                                </Link>
                            ))}
                        </nav>
                        <div className="mt-10 flex flex-col gap-3 border-t border-charcoal/10 pt-8">
                            {auth.user ? (
                                <Link
                                    href={dashboard()}
                                    className={brandButton()}
                                >
                                    My account
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={register()}
                                        className={brandButton()}
                                    >
                                        Register
                                    </Link>
                                    <Link
                                        href={login()}
                                        className={brandButton({
                                            variant: 'secondary',
                                        })}
                                    >
                                        Log in
                                    </Link>
                                </>
                            )}
                        </div>
                    </SheetContent>
                </Sheet>
            </div>
        </header>
    );
}
