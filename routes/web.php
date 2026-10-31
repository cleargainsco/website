<?php

use App\Enums\Permission;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:'.Permission::AdminAccess->value])->withHead(robots: 'noindex, nofollow')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard')->withHead(title: 'Dashboard');
});

require __DIR__.'/storefront.php';
require __DIR__.'/account.php';
require __DIR__.'/admin.php';
require __DIR__.'/settings.php';
