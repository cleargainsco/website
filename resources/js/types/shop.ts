import type { OrderStatus } from '@/types/enums';
import type { Money } from '@/types/money';

export type CartLine = {
    product_id: string;
    slug: string;
    name: string;
    amount: string | null;
    image_url: string | null;
    quantity: number;
    stock: number;
    unit_price: Money;
    total: Money;
};

export type OrderLine = {
    name: string;
    slug: string;
    amount: string | null;
    quantity: number;
    unit_price: Money;
    total: Money;
};

export type Address = {
    name: string;
    phone: string | null;
    line1: string;
    line2: string | null;
    city: string;
    region: string;
    postal_code: string;
    country: string;
};

export type AppliedDiscount = {
    code: string;
    amount: Money;
};

export type Order = {
    id: string;
    number: string;
    status: OrderStatus;
    email: string;
    placed_at: string;
    paid_at: string | null;
    shipped_at: string | null;
    cancelled_at: string | null;
    subtotal: Money;
    discount: AppliedDiscount | null;
    shipping: Money | null;
    tax: Money | null;
    total: Money;
    unit_count?: number;
    lines?: OrderLine[];
    shipping_address: Address;
    customer_note: string | null;
    carrier: string | null;
    tracking_number: string | null;
};

export type AdminOrder = Order & {
    customer_name: string | null;
    research_use_confirmed_at: string | null;
    can_mark_paid: boolean;
    can_ship: boolean;
    can_cancel: boolean;
};
