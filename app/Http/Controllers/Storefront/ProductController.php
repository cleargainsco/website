<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Support\Storefront;
use Inertia\Inertia;
use Inertia\Response;

final class ProductController extends Controller
{
    public function index(Storefront $storefront): Response
    {
        return Inertia::render('marketing/shop/index', [
            'products' => $storefront->products(),
        ]);
    }

    public function show(Storefront $storefront, string $product): Response
    {
        $shown = $storefront->find($product);

        return Inertia::render('marketing/shop/show', [
            'product' => $shown,
            'related' => $storefront->products()
                ->reject(fn (array $other): bool => $other['slug'] === $shown['slug'])
                ->take(3)
                ->values(),
        ]);
    }
}
