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
        Schema::table('request_quotes', function (Blueprint $table) {
            $columns = [
                'source_type' => fn() => $table->string('source_type')->nullable(),
                'source_label' => fn() => $table->string('source_label')->nullable(),
                'time_ago' => fn() => $table->string('time_ago')->nullable(),
                'badge_status' => fn() => $table->string('badge_status')->nullable(),
                'name' => fn() => $table->string('name')->nullable(),
                'phone' => fn() => $table->string('phone')->nullable(),
                'reference_code' => fn() => $table->string('reference_code')->nullable(),
                'vehicle_title' => fn() => $table->string('vehicle_title')->nullable(),
                'vehicle_type' => fn() => $table->string('vehicle_type')->nullable(),
                'service_requested' => fn() => $table->string('service_requested')->nullable(),
                'guide_price' => fn() => $table->decimal('guide_price', 10, 2)->nullable(),
                'preferred_day' => fn() => $table->string('preferred_day')->nullable(),
                'notes' => fn() => $table->text('notes')->nullable(),
                'photos' => fn() => $table->json('photos')->nullable(),
            ];

            foreach ($columns as $column => $definition) {
                if (!Schema::hasColumn('request_quotes', $column)) {
                    $definition();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_quotes', function (Blueprint $table) {
            $table->dropColumn([
                'source_type',
                'source_label',
                'time_ago',
                'badge_status',
                'name',
                'phone',
                'reference_code',
                'vehicle_title',
                'vehicle_type',
                'service_requested',
                'guide_price',
                'preferred_day',
                'notes',
                'photos'
            ]);
        });
    }
};