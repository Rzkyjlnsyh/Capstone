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
        Schema::create('hpe_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('exchange_rate_id')->nullable()->constrained('exchange_rates');
            $table->foreignId('calculated_by')->nullable()->constrained('users');
            $table->decimal('margin_percent', 5, 2)->default(0);
            $table->decimal('total_cost_idr', 18, 2);
            $table->decimal('total_with_margin', 18, 2);
            $table->string('status')->default('draft');
            $table->json('component_breakdown');
            $table->json('warnings')->nullable();
            $table->timestamp('calculated_at')->useCurrent();
            $table->timestamps();
            $table->index(['product_id', 'calculated_at'], 'hpe_results_product_calculated_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hpe_results');
    }
};
