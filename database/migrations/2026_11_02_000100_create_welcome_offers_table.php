<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row: the first-visit offer and the copy an admin edits for it.
     */
    public function up(): void
    {
        Schema::create('welcome_offers', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(false);
            $table->unsignedTinyInteger('percent_off')->default(10);
            // Null: the code never expires.
            $table->unsignedSmallInteger('valid_days')->nullable()->default(30);
            $table->string('eyebrow', 60)->default('Welcome to Clear Gains');
            $table->string('headline', 120)->default('Take 10% off your first order');
            $table->string('body', 500)->default('Join our list for research updates, restocks and new arrivals, and we’ll email you a one-time code for 10% off.');
            $table->string('button_label', 40)->default('Email me my code');
            $table->string('success_headline', 120)->default('Check your inbox');
            $table->string('success_body', 500)->default('Your code is on its way. Enter it at checkout to take 10% off your first order.');
            $table->string('fine_print', 500)->default('One code per email address, valid for 30 days on a single order. By signing up you agree to receive marketing emails; unsubscribe anytime.');
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
