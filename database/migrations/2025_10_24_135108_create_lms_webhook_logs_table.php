<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_webhook_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('event_type')->nullable();
            $table->json('payload');
            $table->json('headers')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_webhook_logs');
    }
};

