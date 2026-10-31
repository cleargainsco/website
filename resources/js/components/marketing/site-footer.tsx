import { Link, usePage } from '@inertiajs/react';
import { policyLinks, primaryLinks } from '@/lib/storefront-links';
import { home } from '@/routes';

export function SiteFooter() {
    const { supportEmail } = usePage().props;

    return (
        <footer className="bg-deep-ocean text-linen">
            <div className="mx-auto grid max-w-7xl gap-12 px-6 py-16 lg:grid-cols-[1.4fr_1fr_1fr_1.2fr] lg:px-8">
                <div>
                    <Link href={home()}>
                        <img
                            src="/images/brand/clear-gains-horizontal-reversed.svg"
                            alt="Clear Gains — Research Peptides"
                            className="h-11 w-auto"
                        />
                    </Link>
                    <p className="mt-6 max-w-xs text-sm leading-relaxed text-linen/70">
                        Research peptides with batch-level documentation,
                        straightforward pricing and real people behind the
                        inbox.
                    </p>
                </div>

                <FooterColumn title="Explore">
                    {primaryLinks.map((link) => (
                        <FooterLink key={link.title} href={link.href}>
                            {link.title}
                        </FooterLink>
                    ))}
                </FooterColumn>

                <FooterColumn title="Policies">
                    {policyLinks.map((link) => (
                        <FooterLink key={link.title} href={link.href}>
                            {link.title}
                        </FooterLink>
                    ))}
                </FooterColumn>

                <FooterColumn title="Support">
                    <a
                        href={`mailto:${supportEmail}`}
                        className="text-sm text-linen/80 hover:text-champagne"
                    >
                        {supportEmail}
                    </a>
                    <p className="text-sm text-linen/60">
                        Include your order or batch number and we'll pick it up
                        from there.
                    </p>
                </FooterColumn>
            </div>

            <div className="border-t border-linen/10">
                <div className="mx-auto flex max-w-7xl flex-col gap-3 px-6 py-6 text-xs text-linen/50 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                    <p>
                        All products are sold for research use only and are not
                        for human or veterinary consumption.
                    </p>
                    <p>
                        © {new Date().getFullYear()} Clear Gains. All rights
                        reserved.
                    </p>
                </div>
            </div>
        </footer>
    );
}

function FooterColumn({
    title,
    children,
}: {
    title: string;
    children: React.ReactNode;
}) {
    return (
        <div>
            <p className="text-xs font-medium tracking-[0.3em] text-champagne uppercase">
                {title}
            </p>
            <div className="mt-5 flex flex-col gap-3">{children}</div>
        </div>
    );
}

function FooterLink({
    href,
    children,
}: {
    href: React.ComponentProps<typeof Link>['href'];
    children: React.ReactNode;
}) {
    return (
        <Link
            href={href}
            className="text-sm text-linen/80 hover:text-champagne"
        >
            {children}
        </Link>
    );
}
