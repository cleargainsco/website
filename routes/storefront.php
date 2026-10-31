<?php

declare(strict_types=1);

use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CartItemController;
use App\Http\Controllers\Storefront\CertificateController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('shop', [ProductController::class, 'index'])->name('shop.index');
Route::get('shop/{product}', [ProductController::class, 'show'])->name('shop.show');

Route::get('testing', [CertificateController::class, 'index'])->name('testing');

Route::get('cart', [CartController::class, 'show'])->name('cart.show')->withHead(title: 'Your cart', robots: 'noindex, follow');

Route::middleware('throttle:60,1')->group(function (): void {
    Route::post('cart/items', [CartItemController::class, 'store'])->name('cart.items.store');
    Route::patch('cart/items/{product}', [CartItemController::class, 'update'])->name('cart.items.update');
    Route::delete('cart/items/{product}', [CartItemController::class, 'destroy'])->name('cart.items.destroy');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('checkout', [CheckoutController::class, 'create'])->name('checkout.create')->withHead(title: 'Checkout', robots: 'noindex, nofollow');
    Route::post('checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');
});

Route::inertia('about', 'marketing/about')->name('about');
Route::inertia('faq', 'marketing/faq')->name('faq');

Route::prefix('policies')->name('policies.')->group(function (): void {
    Route::inertia('shipping', 'marketing/policies/shipping')->name('shipping');
    Route::inertia('returns', 'marketing/policies/returns')->name('returns');
    Route::inertia('privacy', 'marketing/policies/privacy')->name('privacy');
    Route::inertia('terms', 'marketing/policies/terms')->name('terms');
});
