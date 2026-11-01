<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Marketing\UpdateWelcomeOfferAction;
use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateWelcomeOfferRequest;
use App\Models\Subscriber;
use App\Models\WelcomeOffer;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Shop\Models\DiscountCode;

final class WelcomeOfferController extends Controller
{
    public function edit(): Response
    {
        $this->authorize(Permission::MarketingManage->value);

        $offer = WelcomeOffer::current();

        return Inertia::render('admin/welcome-offer/edit', [
            'offer' => [
                'is_enabled' => $offer->is_enabled,
                'percent_off' => $offer->percent_off,
                'valid_days' => $offer->valid_days,
                ...$offer->copy(),
            ],
            'stats' => [
                'codes_sent' => Subscriber::query()->whereNotNull('discount_code_id')->count(),
                'codes_redeemed' => DiscountCode::query()
                    ->whereIn('id', Subscriber::query()->whereNotNull('discount_code_id')->select('discount_code_id'))
                    ->where('times_used', '>', 0)
                    ->count(),
            ],
        ]);
    }

    public function update(UpdateWelcomeOfferRequest $request, UpdateWelcomeOfferAction $action): RedirectResponse
    {
        $action->handle($request->toWelcomeOfferData());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Saved the welcome offer.')]);

        return back();
    }
}
