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
        Schema::create('purchase_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('component_id')->constrained('components')->cascadeOnDelete();
            $table->date('purchase_date');
            $table->string('vendor_name')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->foreignId('exchange_rate_id')->nullable()->constrained('exchange_rates');
            $table->decimal('rate_value_snapshot', 18, 6)->nullable();
            $table->decimal('quantity', 12, 3)->default(1);
            $table->decimal('unit_price_original', 18, 4);
            $table->decimal('unit_price_idr', 18, 4);
            $table->text('notes')->nullable();
            $table->string('document_reference')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['product_id', 'component_id', 'purchase_date'], 'purchase_histories_lookup_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_histories');
    }
};
