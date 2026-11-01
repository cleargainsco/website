import type { SubscriberSource } from '@/types/enums';

export type WelcomeOfferCopy = {
    eyebrow: string;
    headline: string;
    body: string;
    button_label: string;
    success_headline: string;
    success_body: string;
    fine_print: string;
};

export type AdminWelcomeOffer = WelcomeOfferCopy & {
    is_enabled: boolean;
    percent_off: number;
    valid_days: number | null;
};

export type SubscriberRow = {
    id: number;
    email: string;
    source: SubscriberSource;
    source_label: string;
    has_account: boolean;
    marketing_consent_at: string | null;
    discount_code: string | null;
    discount_code_used: boolean;
    synced_to_resend_at: string | null;
    created_at: string | null;
};

export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
};
