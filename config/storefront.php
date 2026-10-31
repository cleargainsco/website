<?php

declare(strict_types=1);

return [

    'support_email' => env('STOREFRONT_SUPPORT_EMAIL', 'support@cleargains.com'),

    'storage' => 'Store lyophilized vials frozen at -20 °C, sealed and away from light. Once reconstituted, keep refrigerated at 2–8 °C and do not refreeze.',

    /*
     * Published certificates of analysis, keyed by product slug, newest first.
     * `report` is a path under public/coas/.
     */
    'batches' => [],

];
