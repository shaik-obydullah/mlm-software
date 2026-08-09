<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->decimal('balance', 15, 2)->default(0)->comment('Available balance');
            $table->decimal('locked_amount', 15, 2)->default(0)->comment('Pending withdrawals / holds');
            $table->decimal('total_earned', 15, 2)->default(0)->comment('Lifetime credited');
            $table->decimal('total_withdrawn', 15, 2)->default(0)->comment('Lifetime withdrawn');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
