<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_page_settings', function (Blueprint $table) {
            $table->id();
            $table->string('page')->unique();
            $table->string('title')->nullable();
            $table->string('description', 500)->nullable();
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
