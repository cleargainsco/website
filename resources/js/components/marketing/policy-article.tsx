import { Link, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { PageHero } from '@/components/marketing/page-hero';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { policyLinks } from '@/lib/storefront-links';
import { cn } from '@/lib/utils';

export function PolicyArticle({
    title,
    summary,
    children,
}: {
    title: string;
    summary: string;
    children: ReactNode;
}) {
    const { isCurrentUrl } = useCurrentUrl();
    const { supportEmail } = usePage().props;

    return (
        <>
            <PageHero eyebrow="Policies" title={title}>
                {summary}
            </PageHero>

            <section className="mx-auto grid max-w-7xl gap-12 px-6 py-20 lg:grid-cols-[14rem_1fr] lg:px-8">
                <nav
                    aria-label="Policies"
                    className="lg:sticky lg:top-28 lg:self-start"
                >
                    <ul className="flex flex-wrap gap-2 lg:flex-col lg:gap-1">
                        {policyLinks.map((link) => (
                            <li key={link.title}>
                                <Link
                                    href={link.href}
                                    className={cn(
                                        'block rounded-full px-4 py-2 text-sm text-charcoal/70 ring-1 ring-charcoal/10 hover:text-deep-ocean lg:rounded-lg lg:ring-0',
                                        isCurrentUrl(link.href)
                                            ? 'bg-deep-ocean text-linen hover:text-linen'
                                            : 'lg:hover:bg-sand/50',
                                    )}
                                >
                                    {link.title}
                                </Link>
                            </li>
                        ))}
                    </ul>
                </nav>

                <article className="max-w-3xl rounded-3xl bg-white p-8 leading-relaxed text-charcoal/80 ring-1 ring-charcoal/5 sm:p-12 [&_h2]:mt-10 [&_h2]:mb-3 [&_h2]:font-serif [&_h2]:text-2xl [&_h2]:text-charcoal [&_h2:first-child]:mt-0 [&_li]:mt-2 [&_p]:mt-4 [&_ul]:mt-4 [&_ul]:list-disc [&_ul]:pl-5">
                    {children}
                    <h2>Questions</h2>
                    <p>
                        Email{' '}
                        <a
                            href={`mailto:${supportEmail}`}
                            className="font-medium text-deep-ocean hover:underline"
                        >
                            {supportEmail}
                        </a>{' '}
                        and we'll be glad to help.
                    </p>
                </article>
            </section>
        </>
    );
}
