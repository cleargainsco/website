<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Support\Storefront;
use Brick\Money\Money;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Head\Enums\OfferAvailability;
use Laravel\Head\Facades\Head;
use Laravel\Head\Facades\Schema;
use Shop\Enums\StockStatus;

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

        $name = trim("{$shown['name']} {$shown['amount']}");
        $price = Money::ofMinor($shown['price']['minor'], $shown['price']['currency']);

        $schema = Schema::product()
            ->name($name)
            ->description($shown['summary'])
            ->offers(Schema::offer()
                ->price((string) $price->getAmount())
                ->currency($shown['price']['currency'])
                ->availability($shown['stock'] === StockStatus::OutOfStock ? OfferAvailability::OutOfStock : OfferAvailability::InStock));

        if ($shown['image_url'] !== null) {
            $schema->image($shown['image_url']);
            Head::ogImage($shown['image_url'], alt: $name);
        }

        Head::title($shown['meta_title'] ?? $name)
            ->description($shown['meta_description'] ?? $shown['summary'])
            ->schema($schema);

        return Inertia::render('marketing/shop/show', [
            'product' => $shown,
            'related' => $storefront->products()
                ->reject(fn (array $other): bool => $other['slug'] === $shown['slug'])
                ->take(3)
                ->values(),
        ]);
    }
}
