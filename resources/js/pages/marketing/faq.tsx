import { Head, Link, usePage } from '@inertiajs/react';
import { Mail, Plus } from 'lucide-react';
import type { ReactNode } from 'react';
import { PageHero } from '@/components/marketing/page-hero';
import { brandButton } from '@/lib/brand-button';
import { policyLinks } from '@/lib/storefront-links';
import { testing } from '@/routes';
import { returns, shipping } from '@/routes/policies';

export default function Faq() {
    const { supportEmail } = usePage().props;

    return (
        <>
            <Head title="FAQ & Contact">
                <meta
                    name="description"
                    content="Answers about ordering, shipping, storage and documentation, and how to reach Clear Gains support."
                />
            </Head>

            <PageHero eyebrow="FAQ & Contact" title="Questions, answered">
                Ordering, shipping, storage and documentation. If you don't see
                your question here, email us.
            </PageHero>

            <section className="mx-auto grid max-w-7xl gap-14 px-6 py-20 lg:grid-cols-[1fr_22rem] lg:px-8">
                <div className="space-y-14">
                    <FaqGroup title="Ordering">
                        <Question q="How do I place an order?">
                            Online checkout is opening soon. Until then, email
                            us the products and quantities you need and we'll
                            confirm availability, your total and how to pay.
                        </Question>
                        <Question q="How does bulk pricing work?">
                            The price per vial drops at 3, 6 and 10 vials of the
                            same product. Tiers are counted per product, so
                            vials of different products aren't combined.
                        </Question>
                        <Question q="Are tax and shipping included?">
                            No. Prices are per vial; tax and shipping are added
                            to your order total.
                        </Question>
                        <Question q="Who can order?">
                            Our products are sold strictly for laboratory
                            research. We cancel orders that indicate human or
                            veterinary use.
                        </Question>
                    </FaqGroup>

                    <FaqGroup title="Shipping">
                        <Question q="When will my order ship?">
                            Orders are packed on business days once payment is
                            confirmed. You'll receive tracking by email as soon
                            as your order is on its way.
                        </Question>
                        <Question q="How are vials packed?">
                            Vials are sealed, labelled with their batch number
                            and packed to protect them from breakage, heat and
                            light in transit. See our{' '}
                            <InlineLink href={shipping()}>
                                shipping policy
                            </InlineLink>{' '}
                            for details.
                        </Question>
                    </FaqGroup>

                    <FaqGroup title="Storage">
                        <Question q="How should I store vials?">
                            Store lyophilized vials frozen at −20 °C, sealed and
                            away from light. Once reconstituted, keep them
                            refrigerated at 2–8 °C and do not refreeze. Each
                            product page lists its storage guidance.
                        </Question>
                        <Question q="What should I do when my order arrives?">
                            Check that the vials are intact and the seals
                            unbroken, then move them to frozen storage.
                        </Question>
                    </FaqGroup>

                    <FaqGroup title="Documentation">
                        <Question q="Where do I find the COA for my vial?">
                            Find the batch number on your vial label, then look
                            up that product and batch on our{' '}
                            <InlineLink href={testing()}>
                                Testing & COAs
                            </InlineLink>{' '}
                            page.
                        </Question>
                        <Question q="What does a certificate of analysis show?">
                            Each certificate identifies the batch, the date it
                            was tested, the laboratory that performed the
                            analysis and the results for that batch.
                        </Question>
                        <Question q="The report for my batch isn't posted. What now?">
                            Email us the batch number and we'll send you the
                            report directly.
                        </Question>
                    </FaqGroup>

                    <FaqGroup title="Support">
                        <Question q="How do I reach you?">
                            Email {supportEmail}. Include your order number or
                            batch number so we can help straight away.
                        </Question>
                        <Question q="Something is wrong with my order.">
                            Contact us as soon as possible with your order
                            number and photos of the issue. Our{' '}
                            <InlineLink href={returns()}>
                                returns policy
                            </InlineLink>{' '}
                            explains how damaged or incorrect items are handled.
                        </Question>
                    </FaqGroup>
                </div>

                <aside className="lg:sticky lg:top-28 lg:self-start">
                    <div
                        id="contact"
                        className="scroll-mt-28 rounded-3xl bg-deep-ocean p-8 text-linen"
                    >
                        <span className="flex size-12 items-center justify-center rounded-full bg-linen/10">
                            <Mail className="size-5 text-champagne" />
                        </span>
                        <h2 className="mt-6 font-serif text-2xl">
                            Contact support
                        </h2>
                        <p className="mt-3 text-sm leading-relaxed text-linen/70">
                            Orders, shipping, storage or documentation: send us
                            a note and include your order or batch number.
                        </p>
                        <a
                            href={`mailto:${supportEmail}`}
                            className={brandButton({
                                variant: 'light',
                                className: 'mt-6 w-full',
                            })}
                        >
                            {supportEmail}
                        </a>
                    </div>
                    <div className="mt-6 rounded-3xl bg-white p-6 ring-1 ring-charcoal/5">
                        <p className="text-xs font-medium tracking-[0.2em] text-charcoal/50 uppercase">
                            Policies
                        </p>
                        <ul className="mt-4 space-y-2">
                            {policyLinks.map((link) => (
                                <li key={link.title}>
                                    <InlineLink href={link.href}>
                                        {link.title}
                                    </InlineLink>
                                </li>
                            ))}
                        </ul>
                    </div>
                </aside>
            </section>
        </>
    );
}

function FaqGroup({ title, children }: { title: string; children: ReactNode }) {
    return (
        <div>
            <h2 className="font-serif text-3xl font-medium text-charcoal">
                {title}
            </h2>
            <div className="mt-6 divide-y divide-charcoal/10 border-y border-charcoal/10">
                {children}
            </div>
        </div>
    );
}

function Question({ q, children }: { q: string; children: ReactNode }) {
    return (
        <details className="group py-5">
            <summary className="flex cursor-pointer list-none items-center justify-between gap-6 font-medium text-charcoal marker:hidden hover:text-deep-ocean [&::-webkit-details-marker]:hidden">
                {q}
                <Plus className="size-5 shrink-0 text-ocean transition-transform group-open:rotate-45" />
            </summary>
            <p className="mt-3 max-w-2xl leading-relaxed text-charcoal/70">
                {children}
            </p>
        </details>
    );
}

function InlineLink({
    href,
    children,
}: {
    href: React.ComponentProps<typeof Link>['href'];
    children: ReactNode;
}) {
    return (
        <Link
            href={href}
            className="font-medium text-deep-ocean underline-offset-2 hover:underline"
        >
            {children}
        </Link>
    );
}
