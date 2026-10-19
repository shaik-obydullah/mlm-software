<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wallet_id')->index();
            $table->string('type', 30)->index()->comment('credit|debit|hold|release|fee');
            $table->string('category', 50)->index()->comment('commission|withdrawal|interest|purchase|bonus|adjustment');
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2)->default(0);
            $table->string('reference', 100)->nullable()->index();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('completed')->comment('pending|completed|failed|reversed');
            $table->timestamps();

            $table->foreign('wallet_id')->references('id')->on('wallets')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
