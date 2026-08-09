<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cv_activations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->timestamp('activation_date');
            $table->timestamp('expiry_date')->nullable();
            $table->string('status', 20)->default('active')->comment('active|expired');
            $table->timestamps();

            $table->foreign('purchase_id')->references('id')->on('cv_purchases')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cv_activations');
    }
};
