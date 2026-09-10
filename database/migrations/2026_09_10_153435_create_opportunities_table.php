<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('ghl_opportunity_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('stage')->default('new');
            $table->string('status')->default('open');
            $table->decimal('value', 10, 2)->default(0.00);
            $table->string('time_in_stage')->nullable();
            $table->boolean('hand_off')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};