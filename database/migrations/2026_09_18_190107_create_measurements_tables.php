<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('measurements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('opportunity_id')->nullable();
            $table->string('asset_id')->nullable();
            $table->text('address');
            $table->string('provider')->nullable();
            $table->date('imagery_date')->nullable();
            $table->string('coordinate_system')->nullable();
            $table->string('target_surface')->nullable();
            $table->decimal('base_area', 12, 2)->default(0);
            $table->decimal('pitch_factor', 8, 4)->default(1);
            $table->decimal('adjusted_area', 12, 2)->default(0);
            $table->boolean('manual_override')->default(false);
            $table->string('confidence_status')->default('normal');
            $table->timestamps();
        });

        Schema::create('measurement_shapes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('measurement_id');
            $table->json('geojson_polygon');
            $table->decimal('calculated_area', 12, 2);
            $table->string('label')->nullable();
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        Schema::create('measurement_evidence', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('measurement_id');
            $table->text('map_viewport')->nullable();
            $table->string('provider_attribution')->nullable();
            $table->text('screenshot_reference_url')->nullable();
            $table->uuid('created_by');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('measurement_evidence');
        Schema::dropIfExists('measurement_shapes');
        Schema::dropIfExists('measurements');
    }
};