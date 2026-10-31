import { Link, usePage } from '@inertiajs/react';
import { Download, FileClock } from 'lucide-react';
import { Eyebrow } from '@/components/marketing/eyebrow';
import { PageHero } from '@/components/marketing/page-hero';
import { show } from '@/routes/shop';
import type { Product } from '@/types/storefront';
import { formatDate } from '@/lib/dates';

const steps = [
    {
        title: 'Find your batch number',
        body: 'It is printed on the vial label and on your packing slip.',
    },
    {
        title: 'Locate the product below',
        body: 'Reports are grouped by product, newest batch first.',
    },
    {
        title: 'Download the report',
        body: 'Each certificate of analysis is a PDF you can keep with your records.',
    },
];

export default function Testing({ products }: { products: Product[] }) {
    const { supportEmail } = usePage().props;

    return (
        <>
            <PageHero
                eyebrow="Testing & COAs"
                title="Every batch, on the record"
            >
                Certificates of analysis for each product, organised by batch.
                Match the batch number on your vial to the report below.
            </PageHero>

            <section className="mx-auto max-w-7xl px-6 py-20 lg:px-8">
                <ol className="grid gap-6 md:grid-cols-3">
                    {steps.map((step, index) => (
                        <li
                            key={step.title}
                            className="rounded-3xl bg-white p-7 ring-1 ring-charcoal/5"
                        >
                            <span className="font-serif text-3xl text-champagne">
                                0{index + 1}
                            </span>
                            <h2 className="mt-3 font-serif text-xl text-charcoal">
                                {step.title}
                            </h2>
                            <p className="mt-2 text-sm leading-relaxed text-charcoal/70">
                                {step.body}
                            </p>
                        </li>
                    ))}
                </ol>

                <div className="mt-20 grid gap-12 lg:grid-cols-[14rem_1fr]">
                    <nav
                        aria-label="Products"
                        className="lg:sticky lg:top-28 lg:self-start"
                    >
                        <Eyebrow>Jump to</Eyebrow>
                        <ul className="mt-4 flex flex-wrap gap-2 lg:flex-col lg:gap-1">
                            {products.map((product) => (
                                <li key={product.slug}>
                                    <a
                                        href={`#${product.slug}`}
                                        className="block rounded-full px-3 py-1.5 text-sm text-charcoal/70 ring-1 ring-charcoal/10 hover:text-deep-ocean lg:rounded-lg lg:ring-0 lg:hover:bg-sand/50"
                                    >
                                        {product.name}{' '}
                                        <span className="text-charcoal/40">
                                            {product.amount}
                                        </span>
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </nav>

                    <div className="space-y-6">
                        {products.map((product) => (
                            <article
                                key={product.slug}
                                id={product.slug}
                                className="scroll-mt-28 rounded-3xl bg-white p-6 ring-1 ring-charcoal/5 sm:p-8"
                            >
                                <div className="flex flex-wrap items-baseline justify-between gap-4">
                                    <h2 className="font-serif text-2xl text-charcoal">
                                        {product.name}{' '}
                                        <span className="text-deep-ocean">
                                            {product.amount}
                                        </span>
                                    </h2>
                                    <Link
                                        href={show(product.slug)}
                                        className="text-sm font-medium text-deep-ocean hover:text-ocean"
                                    >
                                        View product
                                    </Link>
                                </div>

                                {product.batches.length > 0 ? (
                                    <div className="mt-6 overflow-x-auto">
                                        <table className="w-full min-w-[520px] text-left text-sm">
                                            <thead className="text-xs tracking-[0.2em] text-charcoal/50 uppercase">
                                                <tr>
                                                    <th className="pb-3 font-medium">
                                                        Batch
                                                    </th>
                                                    <th className="pb-3 font-medium">
                                                        Tested
                                                    </th>
                                                    <th className="pb-3 font-medium">
                                                        Purity
                                                    </th>
                                                    <th className="pb-3 font-medium">
                                                        Laboratory
                                                    </th>
                                                    <th className="pb-3 text-right font-medium">
                                                        Report
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody className="divide-y divide-charcoal/5">
                                                {product.batches.map(
                                                    (batch) => (
                                                        <tr key={batch.number}>
                                                            <td className="py-3 font-medium text-charcoal">
                                                                {batch.number}
                                                            </td>
                                                            <td className="py-3 text-charcoal/70">
                                                                {formatDate(
                                                                    batch.tested_at,
                                                                )}
                                                            </td>
                                                            <td className="py-3 text-charcoal/70">
                                                                {batch.purity ??
                                                                    '—'}
                                                            </td>
                                                            <td className="py-3 text-charcoal/70">
                                                                {batch.lab ??
                                                                    '—'}
                                                            </td>
                                                            <td className="py-3 text-right">
                                                                <a
                                                                    href={
                                                                        batch.report_url ??
                                                                        undefined
                                                                    }
                                                                    className="inline-flex items-center gap-1.5 font-medium text-deep-ocean hover:text-ocean"
                                                                >
                                                                    <Download className="size-4" />
                                                                    PDF
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    ),
                                                )}
                                            </tbody>
                                        </table>
                                    </div>
                                ) : (
                                    <p className="mt-5 flex items-start gap-3 rounded-2xl bg-linen px-5 py-4 text-sm text-charcoal/70">
                                        <FileClock className="mt-0.5 size-4 shrink-0 text-champagne" />
                                        The report for the current batch hasn't
                                        been posted yet. Email {supportEmail}{' '}
                                        with your batch number and we'll send it
                                        to you.
                                    </p>
                                )}
                            </article>
                        ))}
                    </div>
                </div>
            </section>
        </>
    );
}
