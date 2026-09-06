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
        Schema::create('drug_issuances', function (Blueprint $table) {
            $table->id();
            $table->string('issuance_number')->unique();
            $table->foreignId('ward_id')->constrained()->cascadeOnDelete();
            $table->foreignId('drug_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity_issued');
            $table->date('issuance_date');
            $table->string('batch_number');
            $table->string('strength')->nullable();
            $table->string('dosage_form')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('issued_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drug_issuances');
    }
};
