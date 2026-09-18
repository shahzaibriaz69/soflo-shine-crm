<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('expenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('vendor');
            $table->date('expense_date');
            $table->string('category');
            $table->string('allocation')->nullable();
            $table->string('ghl_job_id')->nullable();
            $table->text('receipt_object_key')->nullable();
            $table->string('ocr_status')->default('pending');
            $table->string('approval_status')->default('pending');
            $table->decimal('total', 10, 2);
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('expense_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('expense_id');
            $table->text('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_amount', 10, 2);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('suggested_category')->nullable();
            $table->decimal('confidence', 5, 2)->nullable();
            $table->string('approval_state')->default('pending');
            $table->timestamps();
        });

        Schema::create('labor_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('job_session_id');
            $table->uuid('staff_id');
            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->decimal('regular_cost', 10, 2)->default(0);
            $table->decimal('other_cost', 10, 2)->default(0);
            $table->string('approval_state')->default('pending');
            $table->timestamps();
        });

        Schema::create('cost_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('job_session_id')->unique();
            $table->decimal('job_revenue', 10, 2)->default(0);
            $table->decimal('materials_cost', 10, 2)->default(0);
            $table->decimal('labor_cost', 10, 2)->default(0);
            $table->decimal('other_direct_cost', 10, 2)->default(0);
            $table->decimal('gross_profit', 10, 2)->default(0);
            $table->decimal('margin_percentage', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('cost_snapshots');
        Schema::dropIfExists('labor_entries');
        Schema::dropIfExists('expense_lines');
        Schema::dropIfExists('expenses');
    }
};