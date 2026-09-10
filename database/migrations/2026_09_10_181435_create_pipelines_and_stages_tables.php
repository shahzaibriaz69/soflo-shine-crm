<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Pipelines Table
        Schema::create('pipelines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('sales'); // 'sales' ya 'recurring'
            $table->timestamps();
        });

        // 2. Stages Table
        Schema::create('stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pipeline_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::table('opportunities', function (Blueprint $table) {
            if (! Schema::hasColumn('opportunities', 'pipeline_id')) {
                $table->foreignId('pipeline_id')->nullable()->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('opportunities', 'stage_id')) {
                $table->foreignId('stage_id')->nullable()->constrained()->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropForeign(['pipeline_id']);
            $table->dropForeign(['stage_id']);
            $table->dropColumn(['pipeline_id', 'stage_id']);
        });

        Schema::dropIfExists('stages');
        Schema::dropIfExists('pipelines');
    }
};
