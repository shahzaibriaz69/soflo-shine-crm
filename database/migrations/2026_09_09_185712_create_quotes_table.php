<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('quote_number')->unique();
            $table->string('ghl_contact_id')->nullable();
            $table->string('ghl_opportunity_id')->nullable();
            $table->string('ghl_asset_id')->nullable();
            $table->string('status')->default('draft');
            $table->string('currency')->default('USD');
            $table->text('scope')->nullable();
            $table->timestamp('expiry')->nullable();
            $table->uuid('selected_package_id')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('deposit', 10, 2)->default(0);
            $table->timestamp('accepted_time')->nullable();
            $table->uuid('immutable_accepted_version_id')->nullable();
            $table->timestamps();
        });

        Schema::create('quote_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('quote_id');
            $table->integer('version_number');
            $table->json('calculation_input_json');
            $table->json('calculation_output_json');
            $table->string('content_hash');
            $table->uuid('created_by');
            $table->timestamps();
        });

        Schema::create('quote_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('quote_version_id');
            $table->string('ghl_product_id')->nullable();
            $table->string('ghl_price_id')->nullable();
            $table->text('description_snapshot');
            $table->string('unit_type');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->json('modifiers')->nullable();
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->boolean('required_flag')->default(true);
            $table->timestamps();
        });

        Schema::create('quote_access_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('hashed_token')->unique();
            $table->uuid('quote_id');
            $table->string('purpose');
            $table->timestamp('expiry');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });

        Schema::create('quote_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('quote_id');
            $table->string('event_type');
            $table->string('actor');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_events');
        Schema::dropIfExists('quote_access_tokens');
        Schema::dropIfExists('quote_lines');
        Schema::dropIfExists('quote_versions');
        Schema::dropIfExists('quotes');
    }
};