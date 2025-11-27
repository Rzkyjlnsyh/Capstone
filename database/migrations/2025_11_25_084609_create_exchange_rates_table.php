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
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->date('rate_date')->index();
            $table->string('base_currency', 3)->default('USD');
            $table->string('quote_currency', 3)->default('IDR');
            $table->decimal('rate_value', 18, 6);
            $table->string('source')->default('JISDOR');
            $table->timestamp('fetched_at')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
            $table->unique(['rate_date', 'base_currency', 'quote_currency'], 'exchange_rates_date_pair_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
