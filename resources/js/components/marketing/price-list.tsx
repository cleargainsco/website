import { Link } from '@inertiajs/react';
import { formatMoney } from '@/lib/money';
import { show } from '@/routes/shop';
import type { Product } from '@/types/storefront';

export function PriceList({ products }: { products: Product[] }) {
    const quantities = products[0]?.tiers.map((tier) => tier.quantity) ?? [];

    return (
        <div className="overflow-x-auto rounded-3xl bg-white ring-1 ring-charcoal/5">
            <table className="w-full min-w-[640px] text-left text-sm">
                <thead className="bg-deep-ocean text-xs tracking-[0.2em] text-linen uppercase">
                    <tr>
                        <th scope="col" className="px-6 py-4 font-medium">
                            Product
                        </th>
                        {quantities.map((quantity) => (
                            <th
                                key={quantity}
                                scope="col"
                                className="px-6 py-4 text-right font-medium"
                            >
                                {quantity} {quantity === 1 ? 'vial' : 'vials'}
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody className="divide-y divide-charcoal/5">
                    {products.map((product) => (
                        <tr key={product.slug} className="even:bg-linen/60">
                            <th scope="row" className="px-6 py-4 font-normal">
                                <Link
                                    href={show(product.slug)}
                                    className="font-serif text-base text-charcoal hover:text-deep-ocean"
                                >
                                    {product.name}
                                </Link>
                                <span className="ml-2 text-charcoal/50">
                                    {product.amount}
                                </span>
                            </th>
                            {product.tiers.map((tier) => (
                                <td
                                    key={tier.quantity}
                                    className="px-6 py-4 text-right"
                                >
                                    <span className="block text-charcoal">
                                        {formatMoney(tier.unit)}
                                        <span className="text-charcoal/50">
                                            {' '}
                                            /vial
                                        </span>
                                    </span>
                                    {tier.quantity > 1 && (
                                        <span className="block text-xs text-charcoal/50">
                                            {formatMoney(tier.total)} total
                                        </span>
                                    )}
                                </td>
                            ))}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
