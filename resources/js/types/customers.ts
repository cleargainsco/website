import type { Money } from '@/types/money';

export type Customer = {
    id: number;
    name: string;
    email: string;
    email_verified: boolean;
    joined_at: string | null;
    orders_count: number;
    spent: Money;
    last_order_at: string | null;
    marketing_consent?: boolean;
};

export type CustomerWelcomeCode = {
    code: string;
    used: boolean;
    expired: boolean;
};
