<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('job_checklists', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('template_name');
            $table->string('version');
            $table->string('service_category_applicability');
            $table->timestamps();
        });

        Schema::create('job_checklist_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('checklist_id');
            $table->string('phase');
            $table->boolean('required_flag')->default(true);
            $table->string('angle_or_label');
            $table->text('instructions')->nullable();
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        Schema::create('job_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ghl_job_opportunity_id')->nullable();
            $table->string('ghl_appointment_id')->nullable();
            $table->uuid('technician_id');
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->string('status')->default('scheduled');
            $table->timestamps();
        });

        Schema::create('job_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('job_session_id');
            $table->string('phase');
            $table->uuid('checklist_item_id')->nullable();
            $table->text('private_object_key');
            $table->timestamp('capture_time');
            $table->uuid('uploader_id');
            $table->string('mime_type');
            $table->bigInteger('file_size');
            $table->string('checksum');
            $table->json('optional_coordinates')->nullable();
            $table->text('caption')->nullable();
            $table->timestamps();
        });

        Schema::create('damage_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('job_session_id');
            $table->uuid('media_id')->nullable();
            $table->text('description');
            $table->string('severity');
            $table->string('location_on_asset')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->uuid('acknowledged_by')->nullable();
            $table->timestamps();
        });

        Schema::create('job_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('job_session_id');
            $table->string('immutable_report_version');
            $table->text('generated_file_path');
            $table->string('content_hash');
            $table->string('delivery_status')->default('pending');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('job_reports');
        Schema::dropIfExists('damage_items');
        Schema::dropIfExists('job_media');
        Schema::dropIfExists('job_sessions');
        Schema::dropIfExists('job_checklist_items');
        Schema::dropIfExists('job_checklists');
    }
};