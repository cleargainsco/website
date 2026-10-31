<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_products', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->string('type');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('summary');
            $table->text('description')->nullable();
            $table->json('details');
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('is_active')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('shop_product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('shop_products')->cascadeOnDelete();
            $table->unsignedInteger('min_quantity');
            $table->unsignedBigInteger('unit_price_minor');
            $table->char('currency', 3);
            $table->timestamps();

            $table->unique(['product_id', 'min_quantity']);
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
