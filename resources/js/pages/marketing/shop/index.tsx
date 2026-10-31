import { Eyebrow } from '@/components/marketing/eyebrow';
import { PageHero } from '@/components/marketing/page-hero';
import { PriceList } from '@/components/marketing/price-list';
import { ProductCard } from '@/components/marketing/product-card';
import { ResearchUseNotice } from '@/components/marketing/research-use-notice';
import type { Product } from '@/types/storefront';

export default function ShopIndex({ products }: { products: Product[] }) {
    return (
        <>
            <PageHero eyebrow="Shop" title="The catalog">
                Every compound is supplied as a lyophilized powder in a sealed
                vial, labelled with its batch number. Prices are per vial and
                drop at 3, 6 and 10 vials of the same product.
            </PageHero>

            <section className="mx-auto max-w-7xl px-6 py-20 lg:px-8">
                <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    {products.map((product) => (
                        <ProductCard key={product.slug} product={product} />
                    ))}
                </div>
            </section>

            <section className="mx-auto max-w-7xl px-6 pb-24 lg:px-8">
                <Eyebrow>Price list</Eyebrow>
                <h2 className="mt-4 font-serif text-3xl font-medium text-charcoal">
                    Bulk pricing at a glance
                </h2>
                <p className="mt-3 max-w-2xl text-charcoal/70">
                    Bulk pricing applies to vials of the same product. Tax and
                    shipping are additional.
                </p>
                <div className="mt-10">
                    <PriceList products={products} />
                </div>
                <ResearchUseNotice className="mt-10" />
            </section>
        </>
    );
}
