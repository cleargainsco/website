<?php

declare(strict_types=1);

use Shop\ProductTypes\SimpleProduct;

return [

    'currency' => 'USD',

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
     * The model orders belong to. Checkout requires an account, so every order has one.
     */
    'customer_model' => 'App\\Models\\User',

    'orders' => [
        'number_prefix' => '#',
    ],

    /*
     * A flat charge per order in major units, e.g. '9.95'. Left empty, shipping is
     * confirmed with the invoice.
     */
    'shipping' => [
        'flat_rate' => null,
    ],

    /*
     * Each product type is a class implementing Shop\Contracts\ProductType, keyed by
     * the value stored on the product. A type declares the extra fields its products
     * carry, which the admin form renders and the request validates.
     */
    'product_types' => [
        'simple' => SimpleProduct::class,
    ],

];
