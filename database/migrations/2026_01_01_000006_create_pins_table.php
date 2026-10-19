<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pins', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->unsignedBigInteger('cv_tier_id')->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable()->index();
            $table->unsignedBigInteger('used_by')->nullable()->index();
            $table->unsignedBigInteger('purchase_id')->nullable()->index();
            $table->string('status', 20)->default('available')->comment('available|assigned|used|expired');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->foreign('cv_tier_id')->references('id')->on('cv_tiers')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->foreign('used_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('purchase_id')->references('id')->on('cv_purchases')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pins');
    }
};
