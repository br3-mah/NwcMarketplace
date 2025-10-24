<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payment_proofs')) {
            Schema::create('payment_proofs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('order_id')->nullable();
                $table->unsignedInteger('user_id')->nullable();
                $table->string('reference')->nullable();
                $table->string('status')->default('pending');
                $table->json('payload')->nullable();
                $table->json('attachments')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedInteger('verified_by')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();

                $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
            });
            
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_proofs');
    }
};
