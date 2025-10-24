<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('order_id')->nullable()->index();
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->string('status')->default('pending');
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->json('items')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};

