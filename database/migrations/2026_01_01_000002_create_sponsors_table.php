<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sponsors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sponsor_id')->index();
            $table->unsignedBigInteger('sponsee_id')->unique();
            $table->string('type', 20)->default('direct')->comment('direct');
            $table->timestamps();

            $table->foreign('sponsor_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('sponsee_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsors');
    }
};
