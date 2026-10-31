import { about, faq, testing } from '@/routes';
import { privacy, returns, shipping, terms } from '@/routes/policies';
import { index as shop } from '@/routes/shop';
import type { NavItem } from '@/types';

export const primaryLinks: NavItem[] = [
    { title: 'Shop', href: shop() },
    { title: 'Testing & COAs', href: testing() },
    { title: 'About', href: about() },
    { title: 'FAQ & Contact', href: faq() },
];

export const policyLinks: NavItem[] = [
    { title: 'Shipping', href: shipping() },
    { title: 'Returns', href: returns() },
    { title: 'Privacy', href: privacy() },
    { title: 'Terms', href: terms() },
];
