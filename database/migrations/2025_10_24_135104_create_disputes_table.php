<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('order_id')->nullable()->index();
            $table->unsignedInteger('buyer_id')->nullable()->index();
            $table->unsignedInteger('seller_id')->nullable()->index();
            $table->string('status')->default('open');
            $table->string('subject')->nullable();
            $table->string('reason')->nullable();
            $table->text('description')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};

