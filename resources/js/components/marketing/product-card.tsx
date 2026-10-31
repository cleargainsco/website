import { Link } from '@inertiajs/react';
import { StockBadge } from '@/components/marketing/stock-badge';
import { ProductVisual } from '@/components/marketing/product-visual';
import { formatMoney } from '@/lib/money';
import { show } from '@/routes/shop';
import type { Product } from '@/types/storefront';

export function ProductCard({ product }: { product: Product }) {
    const bestTier = product.tiers[product.tiers.length - 1];

    return (
        <Link
            href={show(product.slug)}
            className="group flex flex-col rounded-3xl bg-white p-3 ring-1 ring-charcoal/5 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-deep-ocean/10"
        >
            <ProductVisual product={product} />
            <div className="flex flex-1 flex-col px-2 pt-5 pb-2">
                <div className="flex items-start justify-between gap-4">
                    <div>
                        <h3 className="font-serif text-xl text-charcoal group-hover:text-deep-ocean">
                            {product.name}
                        </h3>
                        <p className="mt-1 text-sm text-charcoal/60">
                            {product.amount} per vial
                        </p>
                    </div>
                    <p className="font-serif text-xl text-deep-ocean">
                        {formatMoney(product.price)}
                    </p>
                </div>
                <div className="mt-4 flex items-center justify-between border-t border-charcoal/10 pt-4">
                    <StockBadge status={product.stock} />
                    <p className="text-xs text-charcoal/60">
                        {formatMoney(bestTier.unit)}/vial at {bestTier.quantity}
                        +
                    </p>
                </div>
            </div>
        </Link>
    );
}
