<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('quote_number')->unique();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('package_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('vehicle_size_multiplier', 4, 2)->default(1.00);
            $table->decimal('condition_multiplier', 4, 2)->default(1.00);
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['draft', 'sent', 'accepted', 'declined'])->default('draft');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
