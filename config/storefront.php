<?php

declare(strict_types=1);

return [

    'support_email' => env('STOREFRONT_SUPPORT_EMAIL', 'support@cleargainsco.com'),

    /*
     * Where new-order alerts are sent, so someone can send the invoice.
     */
    'order_notifications_email' => env('STOREFRONT_ORDER_NOTIFICATIONS_EMAIL', env('STOREFRONT_SUPPORT_EMAIL', 'support@cleargainsco.com')),

    'storage' => 'Store lyophilized vials frozen at -20 °C, sealed and away from light. Once reconstituted, keep refrigerated at 2–8 °C and do not refreeze.',

];
