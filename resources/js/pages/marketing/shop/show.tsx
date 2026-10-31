import { Head, Link, usePage } from '@inertiajs/react';
import { ChevronRight, Download, FileClock } from 'lucide-react';
import { useState } from 'react';
import { Eyebrow } from '@/components/marketing/eyebrow';
import { ProductCard } from '@/components/marketing/product-card';
import { ResearchUseNotice } from '@/components/marketing/research-use-notice';
import { StockBadge } from '@/components/marketing/stock-badge';
import { TierPicker } from '@/components/marketing/tier-picker';
import { ProductVisual } from '@/components/marketing/product-visual';
import { brandButton } from '@/lib/brand-button';
import { formatMoney } from '@/lib/money';
import { testing } from '@/routes';
import { index as shop } from '@/routes/shop';
import { StockStatus } from '@/types/enums';
import type { Product } from '@/types/storefront';

export default function ShopShow({
    product,
    related,
}: {
    product: Product;
    related: Product[];
}) {
    const { supportEmail } = usePage().props;
    const [quantity, setQuantity] = useState(product.tiers[0].quantity);
    const tier =
        product.tiers.find((candidate) => candidate.quantity === quantity) ??
        product.tiers[0];
    const latestBatch = product.batches[0];
    const title = `${product.name} ${product.amount}`;

    return (
        <>
            <Head title={title}>
                <meta name="description" content={product.summary} />
            </Head>

            <div className="mx-auto max-w-7xl px-6 pt-8 lg:px-8">
                <nav
                    aria-label="Breadcrumb"
                    className="flex items-center gap-2 text-sm text-charcoal/60"
                >
                    <Link href={shop()} className="hover:text-deep-ocean">
                        Shop
                    </Link>
                    <ChevronRight className="size-4" />
                    <span className="text-charcoal">{title}</span>
                </nav>
            </div>

            <section className="mx-auto grid max-w-7xl gap-12 px-6 py-10 lg:grid-cols-2 lg:gap-16 lg:px-8 lg:py-14">
                <div className="lg:sticky lg:top-28 lg:self-start">
                    <div className="rounded-[2rem] bg-sand/50 p-6 sm:p-10">
                        <ProductVisual
                            product={product}
                            className="mx-auto max-w-md shadow-2xl shadow-deep-ocean/15"
                        />
                    </div>
                </div>

                <div>
                    <Eyebrow>Research peptide</Eyebrow>
                    <h1 className="mt-4 font-serif text-4xl font-medium text-charcoal sm:text-5xl">
                        {product.name}
                    </h1>
                    <p className="mt-2 text-lg text-charcoal/60">
                        {product.amount} per vial
                    </p>

                    <div className="mt-6 flex flex-wrap items-center gap-x-6 gap-y-2">
                        <p className="font-serif text-3xl text-deep-ocean">
                            {formatMoney(product.price)}
                            <span className="ml-1 font-sans text-sm text-charcoal/50">
                                per vial
                            </span>
                        </p>
                        <StockBadge status={product.stock} />
                    </div>

                    <p className="mt-6 leading-relaxed text-charcoal/75">
                        {product.summary}
                    </p>

                    <div className="mt-8 rounded-3xl bg-white p-6 ring-1 ring-charcoal/5">
                        <TierPicker
                            tiers={product.tiers}
                            selected={quantity}
                            onSelect={setQuantity}
                        />
                        <div className="mt-6 flex items-baseline justify-between border-t border-charcoal/10 pt-5">
                            <span className="text-sm text-charcoal/60">
                                {tier.quantity}{' '}
                                {tier.quantity === 1 ? 'vial' : 'vials'} ×{' '}
                                {formatMoney(tier.unit)}
                            </span>
                            <span className="font-serif text-2xl text-charcoal">
                                {formatMoney(tier.total)}
                            </span>
                        </div>
                        <button
                            type="button"
                            disabled
                            className={brandButton({
                                className: 'mt-5 w-full',
                            })}
                        >
                            {product.stock === StockStatus.OutOfStock
                                ? 'Out of stock'
                                : 'Add to cart'}
                        </button>
                        <p className="mt-3 text-center text-xs text-charcoal/60">
                            Online checkout opens soon. To order now, email{' '}
                            <a
                                href={`mailto:${supportEmail}?subject=${encodeURIComponent(`Order: ${title} × ${tier.quantity}`)}`}
                                className="font-medium text-deep-ocean underline-offset-2 hover:underline"
                            >
                                {supportEmail}
                            </a>
                            .
                        </p>
                    </div>

                    <dl className="mt-8 divide-y divide-charcoal/10 border-y border-charcoal/10">
                        <Detail term="Amount per vial">{product.amount}</Detail>
                        <Detail term="Form">Lyophilized powder</Detail>
                        <Detail term="Storage">{product.storage}</Detail>
                        <Detail term="Batch COA">
                            {latestBatch ? (
                                <a
                                    href={latestBatch.report_url}
                                    className="inline-flex items-center gap-2 font-medium text-deep-ocean hover:text-ocean"
                                >
                                    <Download className="size-4" />
                                    Batch {latestBatch.number}
                                    {latestBatch.purity &&
                                        ` · ${latestBatch.purity} purity`}
                                </a>
                            ) : (
                                <span className="inline-flex items-start gap-2">
                                    <FileClock className="mt-0.5 size-4 shrink-0 text-champagne" />
                                    <span>
                                        The report for the current batch will be
                                        posted on our{' '}
                                        <Link
                                            href={testing()}
                                            className="font-medium text-deep-ocean hover:underline"
                                        >
                                            Testing page
                                        </Link>
                                        .
                                    </span>
                                </span>
                            )}
                        </Detail>
                    </dl>

                    <ResearchUseNotice className="mt-8" />
                </div>
            </section>

            {related.length > 0 && (
                <section className="bg-sand/40 py-20">
                    <div className="mx-auto max-w-7xl px-6 lg:px-8">
                        <h2 className="font-serif text-3xl font-medium text-charcoal">
                            More from the catalog
                        </h2>
                        <div className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {related.map((other) => (
                                <ProductCard key={other.slug} product={other} />
                            ))}
                        </div>
                    </div>
                </section>
            )}
        </>
    );
}

function Detail({
    term,
    children,
}: {
    term: string;
    children: React.ReactNode;
}) {
    return (
        <div className="grid gap-1 py-4 sm:grid-cols-[10rem_1fr] sm:gap-6">
            <dt className="text-xs font-medium tracking-[0.2em] text-charcoal/50 uppercase sm:pt-0.5">
                {term}
            </dt>
            <dd className="text-sm leading-relaxed text-charcoal/80">
                {children}
            </dd>
        </div>
    );
}
