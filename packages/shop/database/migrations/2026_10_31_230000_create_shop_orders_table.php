<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_orders', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->string('number')->nullable()->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->string('status');
            $table->string('email');
            $table->char('currency', 3);
            $table->unsignedBigInteger('subtotal_minor');
            $table->unsignedBigInteger('shipping_minor')->nullable();
            $table->unsignedBigInteger('tax_minor')->nullable();
            $table->unsignedBigInteger('total_minor');
            $table->string('shipping_name');
            $table->string('shipping_phone')->nullable();
            $table->string('shipping_line1');
            $table->string('shipping_line2')->nullable();
            $table->string('shipping_city');
            $table->string('shipping_region');
            $table->string('shipping_postal_code');
            $table->string('shipping_country');
            $table->text('customer_note')->nullable();
            $table->json('details');
            $table->string('carrier')->nullable();
            $table->string('tracking_number')->nullable();
            $table->timestamp('placed_at');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'placed_at']);
        });

        Schema::create('shop_order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('shop_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('shop_products')->nullOnDelete();
            $table->string('product_name');
            $table->string('product_slug');
            $table->json('product_details');
            $table->unsignedInteger('quantity');
            $table->char('currency', 3);
            $table->unsignedBigInteger('unit_price_minor');
            $table->unsignedBigInteger('line_total_minor');
            $table->timestamps();
        });
    }

    /**
     * Down migrations are intentionally omitted.
     *
     * This project does not use rollback migrations. Data loss from dropping tables
     * in production is unacceptable; undo a change with a new forward migration.
     */
    public function down(): void {}
};
