<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number')->unique();
            $table->foreignId('drug_id')->constrained('drugs')->cascadeOnDelete();
            $table->enum('level', ['ndoh', 'lae_ams', 'modilon_hospital']);
            $table->unsignedInteger('quantity_system');
            $table->unsignedInteger('quantity_counted');
            $table->integer('quantity_delta');
            $table->enum('reason', [
                'physical_count',
                'damaged',
                'expired',
                'theft_loss',
                'found_stock',
                'correction',
                'other',
            ])->default('physical_count');
            $table->text('notes')->nullable();
            $table->foreignId('adjusted_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('adjusted_at');
            $table->timestamps();

            $table->index(['level', 'adjusted_at']);
            $table->index(['drug_id', 'adjusted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
