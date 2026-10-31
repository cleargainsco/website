import { Link, usePage } from '@inertiajs/react';
import { Menu, ShoppingBag, UserRound } from 'lucide-react';
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
import { index as accountIndex } from '@/routes/account';
import { show as cartShow } from '@/routes/cart';
import { Permission } from '@/types/enums';
import { index as shop } from '@/routes/shop';

export function SiteHeader() {
    const { isCurrentOrParentUrl } = useCurrentUrl();
    const { auth, cartCount } = usePage().props;
    const isAdmin = auth.permissions.includes(Permission.AdminAccess);
    const accountLink = isAdmin
        ? { href: dashboard(), label: 'Dashboard' }
        : { href: accountIndex(), label: 'My account' };

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

                <div className="ml-auto flex items-center lg:hidden">
                    <CartLink count={cartCount} />
                </div>

                <div className="hidden items-center gap-5 lg:flex">
                    <CartLink count={cartCount} />
                    {auth.user ? (
                        <Link
                            href={accountLink.href}
                            className="inline-flex items-center gap-2 text-sm font-medium text-charcoal/70 hover:text-deep-ocean"
                        >
                            <UserRound className="size-4" />
                            {accountLink.label}
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
                                    href={accountLink.href}
                                    className={brandButton()}
                                >
                                    {accountLink.label}
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

function CartLink({ count }: { count: number }) {
    return (
        <Link
            href={cartShow()}
            className="relative rounded-full p-2 text-charcoal/70 transition-colors hover:bg-sand/60 hover:text-deep-ocean"
            aria-label={
                count > 0
                    ? `Cart, ${count} ${count === 1 ? 'item' : 'items'}`
                    : 'Cart'
            }
        >
            <ShoppingBag className="size-5" />
            {count > 0 && (
                <span className="absolute -top-0.5 -right-0.5 flex min-w-5 items-center justify-center rounded-full bg-deep-ocean px-1 text-[0.65rem] leading-5 font-semibold text-linen">
                    {count > 99 ? '99+' : count}
                </span>
            )}
        </Link>
    );
}
