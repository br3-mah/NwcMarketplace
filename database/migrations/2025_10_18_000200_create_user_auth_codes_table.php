<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_auth_codes')) {
            Schema::create('user_auth_codes', function (Blueprint $table) {
                $table->id();
                $table->string('channel', 20);
                $table->string('identifier');
                $table->string('code', 20);
                $table->unsignedBigInteger('user_id')->nullable();
                $table->json('payload')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->unsignedInteger('attempts')->default(0);
                $table->unsignedInteger('max_attempts')->default(5);
                $table->boolean('is_verified')->default(false);
                $table->timestamps();

                $table->index(['channel', 'identifier']);
                $table->index('user_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_auth_codes')) {
            Schema::dropIfExists('user_auth_codes');
        }
    }
};
