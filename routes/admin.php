<?php

declare(strict_types=1);

use App\Enums\Permission;
use App\Http\Controllers\Admin\ArchivedProductController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\EmailPreviewController;
use App\Http\Controllers\Admin\OrderCancellationController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\OrderPaymentController;
use App\Http\Controllers\Admin\OrderShipmentController;
use App\Http\Controllers\Admin\ProductBatchController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductRestorationController;
use App\Http\Controllers\Admin\SeoPageController;
use App\Http\Controllers\Admin\SubscriberController;
use App\Http\Controllers\Admin\WelcomeOfferController;
use App\Http\Controllers\Admin\WelcomeOfferStatusController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:'.Permission::AdminAccess->value])
    ->withHead(robots: 'noindex, nofollow')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('emails', [EmailPreviewController::class, 'index'])->name('emails.index')->withHead(title: 'Emails');
        Route::get('emails/{email}', [EmailPreviewController::class, 'show'])->name('emails.show');

        Route::get('seo', [SeoPageController::class, 'index'])->name('seo.index')->withHead(title: 'SEO');
        Route::put('seo/{page}', [SeoPageController::class, 'update'])->name('seo.update');

        Route::get('customers', [CustomerController::class, 'index'])->name('customers.index')->withHead(title: 'Customers');
        Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');

        Route::get('welcome-offer', [WelcomeOfferController::class, 'edit'])->name('welcome-offer.edit')->withHead(title: 'Welcome offer');
        Route::put('welcome-offer', [WelcomeOfferController::class, 'update'])->name('welcome-offer.update');
        Route::put('welcome-offer/status', [WelcomeOfferStatusController::class, 'update'])->name('welcome-offer.status.update');

        Route::get('subscribers', [SubscriberController::class, 'index'])->name('subscribers.index')->withHead(title: 'Subscribers');

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index')->withHead(title: 'Orders');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{order}/payment', [OrderPaymentController::class, 'store'])->name('orders.payment.store');
        Route::post('orders/{order}/shipment', [OrderShipmentController::class, 'store'])->name('orders.shipment.store');
        Route::post('orders/{order}/cancellation', [OrderCancellationController::class, 'store'])->name('orders.cancellation.store');

        Route::get('products', [ProductController::class, 'index'])->name('products.index')->withHead(title: 'Products');
        Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        Route::scopeBindings()->group(function () {
            Route::post('products/{product}/batches', [ProductBatchController::class, 'store'])->name('products.batches.store');
            Route::delete('products/{product}/batches/{batch}', [ProductBatchController::class, 'destroy'])->name('products.batches.destroy');
        });

        Route::get('products/archived', [ArchivedProductController::class, 'index'])->name('products.archived.index')->withHead(title: 'Archived products');
        Route::post('products/{product}/restoration', [ProductRestorationController::class, 'store'])
            ->withTrashed()
            ->name('products.restoration.store');
    });
