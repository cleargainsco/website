import { formatMoney, savingsPercent } from '@/lib/money';
import { cn } from '@/lib/utils';
import type { PriceTier } from '@/types/storefront';

export function TierPicker({
    tiers,
    selected,
    onSelect,
}: {
    tiers: PriceTier[];
    selected: number;
    onSelect: (quantity: number) => void;
}) {
    const base = tiers[0].unit;

    return (
        <fieldset>
            <legend className="text-xs font-medium tracking-[0.2em] text-charcoal/60 uppercase">
                Quantity
            </legend>
            <div className="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
                {tiers.map((tier) => {
                    const savings = savingsPercent(tier.unit, base);

                    return (
                        <label
                            key={tier.quantity}
                            className={cn(
                                'relative flex cursor-pointer flex-col rounded-2xl border bg-white px-4 py-3 transition-colors has-focus-visible:outline-2 has-focus-visible:outline-ocean',
                                selected === tier.quantity
                                    ? 'border-deep-ocean ring-1 ring-deep-ocean'
                                    : 'border-charcoal/15 hover:border-deep-ocean/50',
                            )}
                        >
                            <input
                                type="radio"
                                name="quantity"
                                value={tier.quantity}
                                checked={selected === tier.quantity}
                                onChange={() => onSelect(tier.quantity)}
                                className="sr-only"
                            />
                            <span className="text-sm font-medium text-charcoal">
                                {tier.quantity}{' '}
                                {tier.quantity === 1 ? 'vial' : 'vials'}
                            </span>
                            <span className="mt-1 text-xs text-charcoal/60">
                                {formatMoney(tier.unit)}/vial
                            </span>
                            {savings > 0 && (
                                <span className="absolute -top-2 right-3 rounded-full bg-champagne px-2 py-0.5 text-[0.65rem] font-semibold text-charcoal">
                                    Save {savings}%
                                </span>
                            )}
                        </label>
                    );
                })}
            </div>
        </fieldset>
    );
}
