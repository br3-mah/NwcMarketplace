<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('order_id')->nullable()->index();
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->string('tracking_number')->unique();
            $table->string('status')->default('pending');
            $table->string('service_code')->nullable();
            $table->string('service_name')->nullable();
            $table->double('cost')->default(0);
            $table->string('currency_sign', 10)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('expected_delivery_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->string('pod_signed_by')->nullable();
            $table->timestamp('pod_signed_at')->nullable();
            $table->json('pod_attachments')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};

