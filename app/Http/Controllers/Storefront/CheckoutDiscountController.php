<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\ApplyDiscountCodeRequest;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Shop\Cart\Cart;
use Shop\Exceptions\DiscountCodeUnavailableException;

final class CheckoutDiscountController extends Controller
{
    public function store(ApplyDiscountCodeRequest $request, #[CurrentUser] User $user, Cart $cart): RedirectResponse
    {
        try {
            $discount = $cart->applyDiscount($request->string('code')->toString(), $user->email);
        } catch (DiscountCodeUnavailableException $exception) {
            throw ValidationException::withMessages(['code' => $exception->getMessage()]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':code applied: :percent% off.', ['code' => $discount->code, 'percent' => $discount->percent_off])]);

        return back();
    }

    public function destroy(Cart $cart): RedirectResponse
    {
        $cart->removeDiscount();

        return back();
    }
}
