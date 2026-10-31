<?php

declare(strict_types=1);

namespace Shop;

use Illuminate\Support\ServiceProvider;
use Shop\Contracts\OrderHistory;
use Shop\Contracts\PriceResolver;
use Shop\Orders\NoOrderHistory;
use Shop\Pricing\TieredPriceResolver;

final class ShopServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/shop.php', 'shop');

        $this->app->singleton(ProductTypeRegistry::class, fn (): ProductTypeRegistry => new ProductTypeRegistry(
            config()->array('shop.product_types'),
        ));

        $this->app->bind(PriceResolver::class, TieredPriceResolver::class);
        $this->app->bind(OrderHistory::class, NoOrderHistory::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../config/shop.php' => config_path('shop.php'),
        ], 'shop-config');
    }
}
