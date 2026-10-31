<?php

declare(strict_types=1);

use App\Http\Controllers\Account\OrderController;
use App\Http\Controllers\Account\OverviewController;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Middleware\RedirectAdminsToDashboard;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', RedirectAdminsToDashboard::class])->withHead(robots: 'noindex, nofollow')->prefix('account')->name('account.')->group(function () {
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit')->withHead(title: 'Profile');

    Route::middleware('verified')->group(function () {
        Route::get('/', [OverviewController::class, 'index'])->name('index')->withHead(title: 'My account');
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index')->withHead(title: 'Orders');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    });
});
