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
        Schema::create('user_auth_codes', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 20);
            $table->string('identifier', 191);
            $table->string('code', 10);
            $table->unsignedInteger('user_id')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('expires_at');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->unsignedTinyInteger('max_attempts')->default(3);
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            $table->index(['identifier', 'channel']);
            $table->index(['user_id', 'channel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_auth_codes');
    }
};
