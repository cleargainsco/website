<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Support\Storefront;
use Inertia\Inertia;
use Inertia\Response;

final class CertificateController extends Controller
{
    public function index(Storefront $storefront): Response
    {
        return Inertia::render('marketing/testing', [
            'products' => $storefront->products(),
        ]);
    }
}
