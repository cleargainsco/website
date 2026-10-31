import { Head, Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';
import { Eyebrow } from '@/components/marketing/eyebrow';
import { PageHero } from '@/components/marketing/page-hero';
import { brandButton } from '@/lib/brand-button';
import { faq, testing } from '@/routes';

const values = [
    {
        title: 'Transparency',
        body: 'Batch numbers on every label, certificates of analysis published by batch, and one price list for everyone. If you want to see how something was made or tested, you should be able to.',
    },
    {
        title: 'Attention to detail',
        body: 'From the fill amount on the vial to the storage guidance on each product page, we treat the small things as the things that matter, because in research they are.',
    },
    {
        title: 'Customer service',
        body: 'You reach people who know the catalog. Questions about an order, a shipment or a report get a real answer, not a template.',
    },
];

export default function About() {
    return (
        <>
            <Head title="About">
                <meta
                    name="description"
                    content="Who Clear Gains is, and our approach to transparency and customer service."
                />
            </Head>

            <PageHero eyebrow="About" title="Who we are">
                Clear Gains is a research peptide supplier built on a simple
                idea: researchers should be able to see exactly what they are
                buying.
            </PageHero>

            <section className="mx-auto grid max-w-7xl items-center gap-14 px-6 py-24 lg:grid-cols-2 lg:px-8">
                <div className="space-y-5 text-lg leading-relaxed text-charcoal/75">
                    <Eyebrow>Our story</Eyebrow>
                    <h2 className="font-serif text-4xl font-medium text-charcoal">
                        Clear by name, clear by design.
                    </h2>
                    <p>
                        Buying research compounds too often means guessing:
                        unclear sourcing, missing paperwork and support that
                        goes quiet after checkout.
                    </p>
                    <p>
                        Clear Gains is built to be the opposite. A focused
                        catalog, each vial labelled with its batch, a
                        certificate of analysis for that batch, and a team that
                        answers when you write.
                    </p>
                </div>
                <img
                    src="/images/vial-and-card-960.jpg"
                    alt="A Clear Gains vial and label card on a stone surface"
                    className="aspect-[4/3] w-full rounded-[2rem] object-cover shadow-2xl shadow-deep-ocean/15"
                />
            </section>

            <section className="bg-sand/40 py-24">
                <div className="mx-auto max-w-7xl px-6 lg:px-8">
                    <Eyebrow>Our approach</Eyebrow>
                    <h2 className="mt-4 font-serif text-4xl font-medium text-charcoal">
                        What you can count on
                    </h2>
                    <div className="mt-12 grid gap-10 md:grid-cols-3">
                        {values.map((value, index) => (
                            <div
                                key={value.title}
                                className="border-t-2 border-champagne pt-6"
                            >
                                <span className="font-serif text-sm text-champagne">
                                    0{index + 1}
                                </span>
                                <h3 className="mt-2 font-serif text-2xl text-charcoal">
                                    {value.title}
                                </h3>
                                <p className="mt-3 leading-relaxed text-charcoal/70">
                                    {value.body}
                                </p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            <section className="mx-auto max-w-3xl px-6 py-24 text-center lg:px-8">
                <img
                    src="/images/brand/clear-gains-icon.svg"
                    alt=""
                    className="mx-auto w-16"
                />
                <h2 className="mt-6 font-serif text-3xl font-medium text-charcoal sm:text-4xl">
                    See the paperwork for yourself
                </h2>
                <p className="mt-4 text-lg text-charcoal/70">
                    Browse our certificates of analysis, or ask us anything
                    about the catalog.
                </p>
                <div className="mt-8 flex flex-wrap justify-center gap-3">
                    <Link href={testing()} className={brandButton()}>
                        Testing & COAs
                        <ArrowRight className="size-4" />
                    </Link>
                    <Link
                        href={faq()}
                        className={brandButton({ variant: 'secondary' })}
                    >
                        Contact us
                    </Link>
                </div>
            </section>
        </>
    );
}
