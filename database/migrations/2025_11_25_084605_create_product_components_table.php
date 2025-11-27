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
        Schema::create('product_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('component_id')->constrained('components')->cascadeOnDelete();
            $table->decimal('quantity', 12, 3)->default(1);
            $table->string('unit_override', 20)->nullable();
            $table->decimal('unit_cost_override', 18, 2)->nullable();
            $table->timestamps();
            $table->unique(['product_id', 'component_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_components');
    }
};
