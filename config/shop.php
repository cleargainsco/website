<?php

declare(strict_types=1);

use App\Handlers\ProductTypes\ResearchPeptideProductType;

return [

    'currency' => env('SHOP_CURRENCY', 'USD'),

    'low_stock_threshold' => 10,

    /*
     * Uploaded product images are re-encoded as WebP and scaled down to fit within
     * this many pixels on their longest side before they are stored.
     */
    'images' => [
        'max_dimension' => 2000,
        'quality' => 80,
    ],

    /*
     * Each product type is a class implementing Shop\Contracts\ProductType, keyed by
     * the value stored on the product. A type declares the extra fields its products
     * carry, which the admin form renders and the request validates.
     */
    'product_types' => [
        ResearchPeptideProductType::KEY => ResearchPeptideProductType::class,
    ],

];
