<?php

declare(strict_types=1);

return [

    'support_email' => env('STOREFRONT_SUPPORT_EMAIL', 'support@cleargains.com'),

    'currency' => 'USD',

    'storage' => 'Store lyophilized vials frozen at -20 °C, sealed and away from light. Once reconstituted, keep refrigerated at 2–8 °C and do not refreeze.',

    /*
     * Prices are per vial, in cents, keyed by the quantity the tier starts at.
     * Batches list the published certificates of analysis, newest first; `report`
     * is a path under public/coas/.
     */
    'products' => [
        [
            'slug' => 'retatrutide-14mg',
            'name' => 'Retatrutide',
            'amount' => '14 mg',
            'summary' => 'Synthetic triple-agonist peptide, supplied as a lyophilized powder for laboratory research.',
            'stock' => 'in_stock',
            'featured' => true,
            'prices' => [1 => 11200, 3 => 10700, 6 => 9900, 10 => 9200],
            'batches' => [],
        ],
        [
            'slug' => 'retatrutide-20mg',
            'name' => 'Retatrutide',
            'amount' => '20 mg',
            'summary' => 'Synthetic triple-agonist peptide in a larger vial, supplied as a lyophilized powder for laboratory research.',
            'stock' => 'in_stock',
            'featured' => false,
            'prices' => [1 => 14900, 3 => 14300, 6 => 13500, 10 => 12800],
            'batches' => [],
        ],
        [
            'slug' => 'klow-80mg',
            'name' => 'KLOW',
            'amount' => '80 mg',
            'summary' => 'Multi-peptide research blend, supplied as a lyophilized powder in a single vial.',
            'stock' => 'in_stock',
            'featured' => true,
            'prices' => [1 => 12500, 3 => 12100, 6 => 11500, 10 => 11200],
            'batches' => [],
        ],
        [
            'slug' => 'ghk-cu-50mg',
            'name' => 'GHK-Cu',
            'amount' => '50 mg',
            'summary' => 'Copper tripeptide complex, supplied as a lyophilized powder for laboratory research.',
            'stock' => 'in_stock',
            'featured' => false,
            'prices' => [1 => 4700, 3 => 4400, 6 => 4000, 10 => 3600],
            'batches' => [],
        ],
        [
            'slug' => 'bpc-157-tb-500-20mg',
            'name' => 'BPC-157 / TB-500',
            'amount' => '20 mg blend',
            'summary' => 'Two-peptide research blend, 20 mg total per vial, supplied as a lyophilized powder.',
            'stock' => 'in_stock',
            'featured' => true,
            'prices' => [1 => 9600, 3 => 9200, 6 => 8600, 10 => 8300],
            'batches' => [],
        ],
        [
            'slug' => 'nad-plus-1000mg',
            'name' => 'NAD+',
            'amount' => '1,000 mg',
            'summary' => 'Nicotinamide adenine dinucleotide, supplied as a lyophilized powder for laboratory research.',
            'stock' => 'in_stock',
            'featured' => true,
            'prices' => [1 => 7900, 3 => 7500, 6 => 6900, 10 => 6300],
            'batches' => [],
        ],
        [
            'slug' => 'mots-c-10mg',
            'name' => 'MOTS-C',
            'amount' => '10 mg',
            'summary' => 'Mitochondrial-derived peptide, supplied as a lyophilized powder for laboratory research.',
            'stock' => 'in_stock',
            'featured' => false,
            'prices' => [1 => 4700, 3 => 4400, 6 => 4000, 10 => 3600],
            'batches' => [],
        ],
    ],

];
