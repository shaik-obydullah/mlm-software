<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interest_accruals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('cv_purchase_id')->nullable()->index();
            $table->decimal('amount', 15, 2)->comment('Interest amount credited');
            $table->decimal('rate', 8, 4)->comment('Rate applied');
            $table->string('period', 20)->comment('e.g. 2026-07');
            $table->string('status', 20)->default('paid')->comment('pending|paid');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('cv_purchase_id')->references('id')->on('cv_purchases')->nullOnDelete();

            $table->unique(['user_id', 'cv_purchase_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interest_accruals');
    }
};
