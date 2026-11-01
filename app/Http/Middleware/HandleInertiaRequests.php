<?php

namespace App\Http\Middleware;

use App\Enums\Permission;
use App\Http\Controllers\Storefront\AgeVerificationController;
use App\Http\Controllers\Storefront\WelcomeOfferClaimController;
use App\Models\WelcomeOffer;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Shop\Cart\Cart;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
                'permissions' => $request->user()?->isAdmin()
                    ? Permission::values()
                    : $request->user()?->getAllPermissions()->pluck('name')->all() ?? [],
            ],
            'supportEmail' => config('storefront.support_email'),
            'cartCount' => fn (): int => app(Cart::class)->count(),
            'ageVerified' => $request->cookie(AgeVerificationController::COOKIE) === '1',
            'welcomeOffer' => fn (): ?array => $this->welcomeOffer($request),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * The popup copy for a guest who hasn't signed up or closed it yet; null otherwise.
     *
     * @return array{eyebrow: string, headline: string, body: string, button_label: string, success_headline: string, success_body: string, fine_print: string}|null
     */
    private function welcomeOffer(Request $request): ?array
    {
        if ($request->user() !== null || $request->hasCookie(WelcomeOfferClaimController::COOKIE)) {
            return null;
        }

        $offer = WelcomeOffer::current();

        return $offer->isEnabled() ? $offer->copy() : null;
    }
}
