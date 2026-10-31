<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The public pages whose search and share metadata can be edited in the admin. Each
 * value is the page's route name.
 */
enum SeoPage: string
{
    case Home = 'home';
    case Shop = 'shop.index';
    case Testing = 'testing';
    case About = 'about';
    case Faq = 'faq';
    case ShippingPolicy = 'policies.shipping';
    case ReturnsPolicy = 'policies.returns';
    case PrivacyPolicy = 'policies.privacy';
    case Terms = 'policies.terms';

    public function label(): string
    {
        return match ($this) {
            self::Home => 'Home',
            self::Shop => 'Shop',
            self::Testing => 'Testing & COAs',
            self::About => 'About',
            self::Faq => 'FAQ & Contact',
            self::ShippingPolicy => 'Shipping policy',
            self::ReturnsPolicy => 'Returns policy',
            self::PrivacyPolicy => 'Privacy policy',
            self::Terms => 'Terms of service',
        };
    }

    public function defaultTitle(): string
    {
        return match ($this) {
            self::Home => 'Research peptides with batch-level COAs',
            self::Shop => 'Shop research peptides',
            default => $this->label(),
        };
    }

    public function defaultDescription(): string
    {
        return match ($this) {
            self::Home => 'Clear Gains supplies research peptides with batch-level certificates of analysis, transparent pricing and responsive support.',
            self::Shop => 'The Clear Gains research peptide catalog, with per-vial and bulk pricing.',
            self::Testing => 'Certificates of analysis for every Clear Gains product, organised by product and batch.',
            self::About => 'Who Clear Gains is, and our approach to transparency and customer service.',
            self::Faq => 'Answers about ordering, shipping, storage and documentation, and how to reach Clear Gains support.',
            self::ShippingPolicy => 'How and when Clear Gains orders are packed, dispatched and tracked.',
            self::ReturnsPolicy => 'What Clear Gains can accept back, and how we handle damaged or incorrect orders.',
            self::PrivacyPolicy => 'What information Clear Gains collects, how we use it and the choices you have.',
            self::Terms => 'The terms that apply when you use this site or buy from Clear Gains.',
        };
    }
}
