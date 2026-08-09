<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index()->comment('Recipient');
            $table->unsignedBigInteger('from_user_id')->nullable()->index()->comment('Origin of the commission');
            $table->unsignedBigInteger('cv_purchase_id')->nullable()->index();
            $table->string('type', 30)->index()->comment('sponsor|matching|level|royalty');
            $table->decimal('amount', 15, 2);
            $table->decimal('cv_volume', 15, 2)->default(0)->comment('CV volume that generated this commission');
            $table->unsignedInteger('level')->nullable()->comment('Level for type=level');
            $table->string('status', 20)->default('pending')->comment('pending|paid|reversed');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('from_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('cv_purchase_id')->references('id')->on('cv_purchases')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
