<?php

declare(strict_types=1);

use App\Http\Controllers\Storefront\CertificateController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('shop', [ProductController::class, 'index'])->name('shop.index');
Route::get('shop/{product}', [ProductController::class, 'show'])->name('shop.show');

Route::get('testing', [CertificateController::class, 'index'])->name('testing');

Route::inertia('about', 'marketing/about')->name('about');
Route::inertia('faq', 'marketing/faq')->name('faq');

Route::prefix('policies')->name('policies.')->group(function (): void {
    Route::inertia('shipping', 'marketing/policies/shipping')->name('shipping');
    Route::inertia('returns', 'marketing/policies/returns')->name('returns');
    Route::inertia('privacy', 'marketing/policies/privacy')->name('privacy');
    Route::inertia('terms', 'marketing/policies/terms')->name('terms');
});
