<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('product_id')->constrained('shop_products')->cascadeOnDelete();
            $table->string('number');
            $table->date('tested_at');
            $table->string('purity')->nullable();
            $table->string('lab')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'number']);
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
