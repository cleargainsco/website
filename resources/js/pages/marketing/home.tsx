import { Link } from '@inertiajs/react';
import { ArrowRight, FileCheck2, MessagesSquare, Tags } from 'lucide-react';
import { Eyebrow } from '@/components/marketing/eyebrow';
import { MountainRange } from '@/components/marketing/mountain-range';
import { ProductCard } from '@/components/marketing/product-card';
import { brandButton } from '@/lib/brand-button';
import { testing } from '@/routes';
import { index as shop } from '@/routes/shop';
import type { Product } from '@/types/storefront';

const principles = [
    {
        icon: FileCheck2,
        title: 'Documented by batch',
        body: 'Every vial carries a batch number, and every batch has its certificate of analysis on our Testing page, ready to read before you buy.',
    },
    {
        icon: Tags,
        title: 'Pricing you can see',
        body: 'One clear price per vial, with bulk tiers at 3, 6 and 10 vials of the same product. No codes, no surprises at checkout.',
    },
    {
        icon: MessagesSquare,
        title: 'Support that answers',
        body: 'Questions about an order, storage or a report go to people who know the catalog, and who write back.',
    },
];

const motto = ['Cleaner', 'Purer', 'Brighter', 'Stronger', 'Progress'];

export default function Home({ products }: { products: Product[] }) {
    return (
        <>
            <section className="relative overflow-hidden">
                <div className="mx-auto grid max-w-7xl items-center gap-14 px-6 pt-14 pb-32 lg:grid-cols-[1fr_1.1fr] lg:px-8 lg:pt-20 lg:pb-44">
                    <div>
                        <Eyebrow>Research peptides</Eyebrow>
                        <h1 className="mt-5 font-serif text-5xl leading-[1.05] font-medium text-charcoal sm:text-6xl">
                            Clarity in{' '}
                            <span className="text-deep-ocean">every vial.</span>
                        </h1>
                        <p className="mt-6 max-w-lg text-lg leading-relaxed text-charcoal/70">
                            Clear Gains supplies research-grade peptides with
                            the paperwork to match: batch-level certificates of
                            analysis, straightforward pricing and support from
                            people who know the catalog.
                        </p>
                        <div className="mt-9 flex flex-wrap gap-3">
                            <Link href={shop()} className={brandButton()}>
                                Shop the catalog
                                <ArrowRight className="size-4" />
                            </Link>
                            <Link
                                href={testing()}
                                className={brandButton({
                                    variant: 'secondary',
                                })}
                            >
                                View testing & COAs
                            </Link>
                        </div>
                        <dl className="mt-12 grid max-w-md grid-cols-3 gap-6 border-t border-charcoal/10 pt-6">
                            <HeroFact term="Batch COAs" detail="Published" />
                            <HeroFact term="Bulk tiers" detail="3 · 6 · 10" />
                            <HeroFact term="Use" detail="Research only" />
                        </dl>
                    </div>

                    <div className="relative">
                        <div className="absolute -inset-6 -z-10 rounded-[2.5rem] bg-sand/60" />
                        <img
                            src="/images/vial-and-card.jpg"
                            srcSet="/images/vial-and-card-960.jpg 960w, /images/vial-and-card.jpg 1536w"
                            sizes="(min-width: 1024px) 50vw, 100vw"
                            alt="A Clear Gains research peptide vial beside its matching label card"
                            className="aspect-[3/2] w-full rounded-[2rem] object-cover shadow-2xl shadow-deep-ocean/20"
                        />
                    </div>
                </div>
                <MountainRange className="absolute inset-x-0 bottom-0 h-20 lg:h-28" />
            </section>

            <section className="bg-charcoal">
                <ul className="mx-auto flex max-w-7xl flex-wrap items-center justify-center gap-x-6 gap-y-2 px-6 py-5 text-xs tracking-[0.35em] text-linen/80 uppercase lg:px-8">
                    {motto.map((word, index) => (
                        <li key={word} className="flex items-center gap-6">
                            {index > 0 && (
                                <span className="size-1 rounded-full bg-champagne" />
                            )}
                            {word}
                        </li>
                    ))}
                </ul>
            </section>

            <section className="mx-auto max-w-7xl px-6 py-24 lg:px-8">
                <div className="max-w-2xl">
                    <Eyebrow>Our approach</Eyebrow>
                    <h2 className="mt-4 font-serif text-4xl font-medium text-charcoal">
                        Attention to detail, on the record.
                    </h2>
                </div>
                <div className="mt-14 grid gap-6 md:grid-cols-3">
                    {principles.map((principle) => (
                        <div
                            key={principle.title}
                            className="rounded-3xl bg-white p-8 ring-1 ring-charcoal/5"
                        >
                            <span className="flex size-12 items-center justify-center rounded-full bg-mist/50 text-deep-ocean">
                                <principle.icon className="size-5" />
                            </span>
                            <h3 className="mt-6 font-serif text-2xl text-charcoal">
                                {principle.title}
                            </h3>
                            <p className="mt-3 leading-relaxed text-charcoal/70">
                                {principle.body}
                            </p>
                        </div>
                    ))}
                </div>
            </section>

            <section className="bg-sand/40 py-24">
                <div className="mx-auto max-w-7xl px-6 lg:px-8">
                    <div className="flex flex-wrap items-end justify-between gap-6">
                        <div>
                            <Eyebrow>Featured</Eyebrow>
                            <h2 className="mt-4 font-serif text-4xl font-medium text-charcoal">
                                From the catalog
                            </h2>
                        </div>
                        <Link
                            href={shop()}
                            className="inline-flex items-center gap-2 text-sm font-medium text-deep-ocean hover:text-ocean"
                        >
                            View all products
                            <ArrowRight className="size-4" />
                        </Link>
                    </div>
                    <div className="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        {products.map((product) => (
                            <ProductCard key={product.slug} product={product} />
                        ))}
                    </div>
                </div>
            </section>

            <section className="mx-auto max-w-7xl px-6 py-24 lg:px-8">
                <div className="relative overflow-hidden rounded-[2.5rem] bg-deep-ocean px-8 py-16 text-linen sm:px-14 lg:py-20">
                    <img
                        src="/images/brand/clear-gains-icon-reversed.svg"
                        alt=""
                        className="pointer-events-none absolute -right-10 -bottom-10 w-80 opacity-15"
                    />
                    <div className="relative max-w-2xl">
                        <Eyebrow className="text-mist">
                            Testing & documentation
                        </Eyebrow>
                        <h2 className="mt-4 font-serif text-4xl font-medium sm:text-5xl">
                            Every batch, on the record.
                        </h2>
                        <p className="mt-5 text-lg leading-relaxed text-linen/75">
                            Find the certificate of analysis for any product and
                            batch. Match the batch number on your vial label,
                            then download the report.
                        </p>
                        <Link
                            href={testing()}
                            className={brandButton({
                                variant: 'light',
                                className: 'mt-9',
                            })}
                        >
                            Browse COAs
                            <ArrowRight className="size-4" />
                        </Link>
                    </div>
                </div>
            </section>
        </>
    );
}

function HeroFact({ term, detail }: { term: string; detail: string }) {
    return (
        <div>
            <dt className="text-xs tracking-[0.2em] text-charcoal/50 uppercase">
                {term}
            </dt>
            <dd className="mt-1 font-serif text-lg text-deep-ocean">
                {detail}
            </dd>
        </div>
    );
}
