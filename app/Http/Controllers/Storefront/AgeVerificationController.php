<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cookie;

final class AgeVerificationController extends Controller
{
    public const string COOKIE = 'age_verified';

    public const int REMEMBER_DAYS = 30;

    public function store(): RedirectResponse
    {
        Cookie::queue(self::COOKIE, '1', self::REMEMBER_DAYS * 24 * 60);

        return back();
    }
}
