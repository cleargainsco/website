import { StockStatus } from '@/types/enums';

export const stockLabel: Record<StockStatus, string> = {
    [StockStatus.InStock]: 'In stock',
    [StockStatus.LowStock]: 'Low stock',
    [StockStatus.OutOfStock]: 'Out of stock',
};

export const stockDotClass: Record<StockStatus, string> = {
    [StockStatus.InStock]: 'bg-emerald-600',
    [StockStatus.LowStock]: 'bg-champagne',
    [StockStatus.OutOfStock]: 'bg-charcoal/40',
};
