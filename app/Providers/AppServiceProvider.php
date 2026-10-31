<?php

namespace App\Providers;

use App\Enums\SeoPage;
use App\Listeners\SendOrderEmails;
use App\Models\ProductBatch;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Head\Enums\ImageType;
use Laravel\Head\Enums\OgType;
use Laravel\Head\Enums\TwitterCard;
use Laravel\Head\ErrorPages;
use Laravel\Head\Facades\Head;
use Laravel\Head\Facades\Schema;
use Laravel\Head\HeadBuilder;
use Shop\Models\Order;
use Shop\Models\Product;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);

        Product::resolveRelationUsing('batches', fn (Product $product) => $product->hasMany(ProductBatch::class));

        JsonResource::withoutWrapping();

        Event::subscribe(SendOrderEmails::class);

        $this->configureHead();
    }

    private function configureHead(): void
    {
        $name = config()->string('app.name');

        Head::defaults(fn (HeadBuilder $head) => $head
            ->title($name, suffix: " | {$name}")
            ->description(SeoPage::Home->defaultDescription())
            ->canonical()
            ->og(siteName: $name, type: OgType::Website)
            ->ogImage(asset('og-image.png'), width: 1200, height: 630, type: ImageType::Png)
            ->twitter(card: TwitterCard::SummaryWithLargeImage)
            ->searchableByRobots()
            ->schema(Schema::organization()
                ->name($name)
                ->url(url('/'))
                ->logo(asset('images/email/clear-gains-logo.png'))));

        Head::inertiaGlobals(fn (HeadBuilder $head) => $head
            ->viewport('width=device-width, initial-scale=1')
            ->favicon('/favicon.ico', sizes: '48x48')
            ->favicon('/favicon.svg', type: ImageType::Svg)
            ->appleTouchIcon('/apple-touch-icon.png')
            ->manifest('/site.webmanifest')
            ->themeColor('#0F4C5C'));

        Head::errors(function (ErrorPages $errors): void {
            $errors->defaults(robots: 'noindex, follow');
            $errors->status(404, title: 'Page not found');
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
