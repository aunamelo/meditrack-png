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
        Schema::create('supplier_quotes', function (Blueprint $table) {
            $table->id();
            $table->string('drug_name');
            $table->string('dosage');
            $table->string('supplier_name');
            $table->string('country')->nullable();
            $table->decimal('unit_price', 12, 4);
            $table->string('quote_currency', 3)->default('USD');
            $table->unsignedInteger('min_order_qty')->nullable();
            $table->unsignedSmallInteger('lead_time_days')->nullable();
            $table->enum('source', ['overseas', 'local', 'donation'])->default('overseas');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['drug_name', 'dosage']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_quotes');
    }
};
