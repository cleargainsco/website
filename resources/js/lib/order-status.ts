import { OrderStatus } from '@/types/enums';

export const orderStatusLabel: Record<OrderStatus, string> = {
    [OrderStatus.AwaitingPayment]: 'Awaiting payment',
    [OrderStatus.Paid]: 'Paid',
    [OrderStatus.Shipped]: 'Shipped',
    [OrderStatus.Cancelled]: 'Cancelled',
};

export const orderStatusClass: Record<OrderStatus, string> = {
    [OrderStatus.AwaitingPayment]:
        'bg-champagne/25 text-charcoal ring-champagne/50',
    [OrderStatus.Paid]: 'bg-mist/60 text-deep-ocean ring-ocean/30',
    [OrderStatus.Shipped]: 'bg-emerald-50 text-emerald-800 ring-emerald-600/20',
    [OrderStatus.Cancelled]: 'bg-charcoal/5 text-charcoal/60 ring-charcoal/10',
};
