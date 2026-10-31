<?php

declare(strict_types=1);

use Shop\ProductTypes\SimpleProduct;

return [

    'currency' => 'USD',

    'low_stock_threshold' => 10,

    /*
     * Each product type is a class implementing Shop\Contracts\ProductType, keyed by
     * the value stored on the product. A type declares the extra fields its products
     * carry, which the admin form renders and the request validates.
     */
    'product_types' => [
        'simple' => SimpleProduct::class,
    ],

];
