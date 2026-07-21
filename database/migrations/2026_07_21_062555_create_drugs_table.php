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
        Schema::create('drugs', function (Blueprint $table) {
            $table->id();
            $table->string('drug_name');
            $table->text('description')->nullable();
            $table->string('dosage');
            $table->enum('dosage_form', ['tablet', 'injection', 'syrup', 'cream', 'ointment', 'other']);
            $table->string('batch_number')->unique();
            $table->date('expiry_date');
            $table->integer('quantity_received');
            $table->integer('quantity_on_hand')->default(0);
            $table->integer('reorder_point')->default(100);
            $table->string('unit');
            $table->string('supplier')->nullable();
            $table->decimal('cost_per_unit', 8, 2)->nullable();
            $table->string('storage_location')->nullable();
            $table->enum('level', ['ndoh', 'lae_ams', 'modilon_hospital']);
            $table->enum('status', ['active', 'expired', 'written_off'])->default('active');
            $table->timestamp('received_date')->nullable();
            $table->timestamp('last_issued_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drugs');
    }
};
