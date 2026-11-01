<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\ResendServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    ResendServiceProvider::class,
];
