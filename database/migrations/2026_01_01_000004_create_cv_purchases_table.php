<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cv_purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('tier_id')->index();
            $table->unsignedBigInteger('amount')->comment('CV amount purchased');
            $table->decimal('price', 15, 2)->comment('Amount paid in money');
            $table->string('payment_method', 50)->default('bank')->comment('bank|wallet|pin');
            $table->string('status', 20)->default('pending')->comment('pending|paid|activated|cancelled');
            $table->string('transaction_id', 100)->nullable()->index();
            $table->string('pin_code', 50)->nullable()->comment('PIN used for activation');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('tier_id')->references('id')->on('cv_tiers')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cv_purchases');
    }
};
