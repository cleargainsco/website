<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row: the home page hero copy an admin edits. The hero image is a media item on it.
     */
    public function up(): void
    {
        Schema::create('home_pages', function (Blueprint $table) {
            $table->id();
            $table->string('eyebrow', 60)->default('Research peptides');
            $table->string('title', 80)->default('Clarity in');
            $table->string('title_highlight', 80)->nullable()->default('every vial.');
            $table->string('description', 400)->default('Clear Gains supplies research-grade peptides with the paperwork to match: batch-level certificates of analysis, straightforward pricing and support from people who know the catalog.');
            $table->string('image_alt', 160)->default('A Clear Gains research peptide vial beside its matching label card');
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
