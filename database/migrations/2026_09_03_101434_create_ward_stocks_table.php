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
        Schema::create('ward_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ward_id')->constrained()->cascadeOnDelete();
            $table->foreignId('drug_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity_on_hand')->default(0);
            $table->unsignedInteger('reorder_point')->default(10);
            $table->timestamp('last_issued_at')->nullable();
            $table->timestamps();

            $table->unique(['ward_id', 'drug_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ward_stocks');
    }
};
