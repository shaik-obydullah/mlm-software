<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('type', 30)->index()->comment('sponsor|matching|level|royalty');
            $table->decimal('rate', 8, 4)->comment('Commission rate e.g. 10.0000 = 10%');
            $table->unsignedBigInteger('min_volume')->default(0)->comment('Minimum CV volume to qualify');
            $table->unsignedInteger('level')->nullable()->comment('Level for type=level');
            $table->string('status', 20)->default('active')->comment('active|inactive');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_rules');
    }
};
