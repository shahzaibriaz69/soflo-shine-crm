<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('app_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email')->unique();
            $table->string('name');
            $table->string('status')->default('active');
            $table->string('ghl_user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('app_roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->json('permissions')->nullable();
            $table->timestamps();
        });

        Schema::create('app_user_roles', function (Blueprint $table) {
            $table->uuid('user_id');
            $table->uuid('role_id');
            $table->primary(['user_id', 'role_id']);
        });

        Schema::create('external_id_map', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('internal_entity_type');
            $table->uuid('internal_id');
            $table->string('external_system');
            $table->string('external_id');
            $table->timestamps();
        });

        Schema::create('outbox_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('intended_ghl_write');
            $table->string('idempotency_key')->unique();
            $table->string('status')->default('pending');
            $table->integer('attempts')->default(0);
            $table->timestamp('next_retry')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('actor');
            $table->string('action');
            $table->string('entity');
            $table->json('before_summary')->nullable();
            $table->json('after_summary')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('audit_events');
        Schema::dropIfExists('outbox_events');
        Schema::dropIfExists('external_id_map');
        Schema::dropIfExists('app_user_roles');
        Schema::dropIfExists('app_roles');
        Schema::dropIfExists('app_users');
    }
};