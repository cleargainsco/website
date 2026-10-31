<?php

use App\Enums\Permission;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:'.Permission::AdminAccess->value])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/storefront.php';
require __DIR__.'/account.php';
require __DIR__.'/admin.php';
require __DIR__.'/settings.php';
