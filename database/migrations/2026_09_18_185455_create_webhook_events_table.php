<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('provider_event_id')->unique(); // GHL event ID or deterministic hash
            $table->string('event_type');
            $table->timestamp('received_time');
            $table->string('processing_status')->default('pending'); // pending, processed, failed
            $table->integer('attempt_count')->default(0);
            $table->json('redacted_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
    }
};