<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('catalog_extensions')) {
            Schema::create('catalog_extensions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('ghl_product_id');
                $table->string('ghl_price_id')->nullable();
                $table->string('unit_type');
                $table->string('display_unit');
                $table->decimal('minimum_charge', 10, 2)->default(0);
                $table->boolean('starting_at_flag')->default(false);
                $table->string('formula_key')->nullable();
                $table->boolean('active_state')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('modifier_sets')) {
            Schema::create('modifier_sets', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('scope');
                $table->string('name');
                $table->json('selection_values');
                $table->decimal('multiplier_or_adjustment', 10, 2);
                $table->date('effective_from')->nullable();
                $table->date('effective_to')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('packages')) {
            Schema::create('packages', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->string('subtitle')->nullable();
                $table->boolean('featured_flag')->default(false);
                $table->string('discount_type')->nullable();
                $table->decimal('discount_value', 10, 2)->default(0);
                $table->boolean('active_state')->default(true);
                $table->integer('display_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('package_items')) {
            Schema::create('package_items', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('package_id');
                $table->uuid('product_id');
                $table->decimal('default_quantity', 10, 2)->default(1);
                $table->boolean('required_flag')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('upsell_rules')) {
            Schema::create('upsell_rules', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('trigger_product_or_category');
                $table->string('offered_product');
                $table->integer('priority')->default(0);
                $table->json('applicability_conditions')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('plan_rules')) {
            Schema::create('plan_rules', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('cadence');
                $table->decimal('discount', 10, 2)->default(0);
                $table->integer('minimum_commitment')->default(1);
                $table->boolean('recurrence')->default(true);
                $table->date('effective_from')->nullable();
                $table->date('effective_to')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void {
        Schema::dropIfExists('plan_rules');
        Schema::dropIfExists('upsell_rules');
        Schema::dropIfExists('package_items');
        Schema::dropIfExists('packages');
        Schema::dropIfExists('modifier_sets');
        Schema::dropIfExists('catalog_extensions');
    }
};