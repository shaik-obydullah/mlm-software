<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interest_rates', function (Blueprint $table) {
            $table->id();
            $table->decimal('rate', 8, 4)->comment('Monthly interest rate percent');
            $table->date('effective_date')->index();
            $table->string('status', 20)->default('active')->comment('active|inactive');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interest_rates');
    }
};
