<?php

declare(strict_types=1);

use App\Enums\Permission;
use App\Http\Controllers\Admin\ArchivedProductController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductRestorationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:'.Permission::AdminAccess->value])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        Route::get('products/archived', [ArchivedProductController::class, 'index'])->name('products.archived.index');
        Route::post('products/{product}/restoration', [ProductRestorationController::class, 'store'])
            ->withTrashed()
            ->name('products.restoration.store');
    });
