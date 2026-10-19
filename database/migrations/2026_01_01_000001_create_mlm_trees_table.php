<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mlm_trees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->unsignedBigInteger('parent_id')->nullable()->index()->comment('Parent node in binary tree');
            $table->string('position', 10)->nullable()->comment('left|right');
            $table->unsignedInteger('level')->default(0)->index()->comment('Depth in binary tree');
            $table->string('path', 500)->nullable()->index()->comment('Materialized ancestor path e.g. /1/5/9/');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('parent_id')->references('id')->on('mlm_trees')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mlm_trees');
    }
};
