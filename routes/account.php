<?php

declare(strict_types=1);

use App\Http\Controllers\Account\ProfileController;
use App\Http\Middleware\RedirectAdminsToDashboard;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', RedirectAdminsToDashboard::class])->prefix('account')->name('account.')->group(function () {
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');

    Route::middleware('verified')->group(function () {
        Route::inertia('/', 'account/index')->name('index');
        Route::inertia('orders', 'account/orders')->name('orders.index');
    });
});
