<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id', 30)->unique()->comment('Public MLM member ID');
            $table->unsignedBigInteger('sponsor_id')->nullable()->index()->comment('User who referred this user');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone', 30)->nullable();
            $table->string('role', 20)->default('member')->comment('admin|agent|member');
            $table->string('status', 20)->default('pending')->comment('pending|active|inactive|suspended');
            $table->string('kyc_status', 20)->default('not_submitted')->comment('not_submitted|pending|approved|rejected');
            $table->string('nominee_name')->nullable();
            $table->string('nominee_relation', 50)->nullable();
            $table->string('nominee_phone', 30)->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number', 50)->nullable();
            $table->string('bank_ifsc', 30)->nullable();
            $table->decimal('balance', 15, 2)->default(0)->comment('Cached available wallet balance');
            $table->timestamp('activated_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('sponsor_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
