<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('shipment_id')->index();
            $table->string('event_code')->nullable();
            $table->string('status');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_events');
    }
};

