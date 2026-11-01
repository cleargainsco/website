<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateWelcomeOfferStatusRequest;
use App\Models\WelcomeOffer;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final class WelcomeOfferStatusController extends Controller
{
    public function update(UpdateWelcomeOfferStatusRequest $request): RedirectResponse
    {
        $enabled = $request->boolean('is_enabled');

        WelcomeOffer::current()->update(['is_enabled' => $enabled]);

        Inertia::flash('toast', ['type' => 'success', 'message' => $enabled ? __('The welcome offer is live.') : __('The welcome offer is off.')]);

        return back();
    }
}
