import type { Money } from '@/types/money';

export function minorUnitsPerMajor(currency: string): number {
    const digits =
        new Intl.NumberFormat(undefined, {
            style: 'currency',
            currency,
        }).resolvedOptions().maximumFractionDigits ?? 2;

    return 10 ** digits;
}

export function formatMoney(money: Money): string {
    const scale = minorUnitsPerMajor(money.currency);

    return new Intl.NumberFormat(undefined, {
        style: 'currency',
        currency: money.currency,
        minimumFractionDigits: money.minor % scale === 0 ? 0 : undefined,
    }).format(money.minor / scale);
}

export function savingsPercent(unit: Money, base: Money): number {
    return Math.round(((base.minor - unit.minor) / base.minor) * 100);
}
