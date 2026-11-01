<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront;

use App\Actions\Marketing\ClaimWelcomeOfferAction;
use App\Exceptions\WelcomeOfferClosedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\ClaimWelcomeOfferRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\ValidationException;

final class WelcomeOfferClaimController extends Controller
{
    /**
     * Set once the visitor signs up or closes the popup, so it is not shown again.
     */
    public const string COOKIE = 'welcome_offer_seen';

    public const int REMEMBER_DAYS = 365;

    public function store(ClaimWelcomeOfferRequest $request, ClaimWelcomeOfferAction $claim): RedirectResponse
    {
        try {
            $claim->handle($request->string('email')->toString());
        } catch (WelcomeOfferClosedException $exception) {
            throw ValidationException::withMessages(['email' => $exception->getMessage()]);
        }

        Cookie::queue(self::COOKIE, '1', self::REMEMBER_DAYS * 24 * 60, httpOnly: false);

        return back();
    }
}
