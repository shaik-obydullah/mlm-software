<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cv_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->unsignedBigInteger('amount')->comment('CV value of the package');
            $table->decimal('price', 15, 2)->nullable()->comment('Monetary price if different from CV');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active')->comment('active|inactive');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cv_tiers');
    }
};
