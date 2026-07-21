<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('drug_name');
            $table->string('dosage');
            $table->unsignedInteger('quantity_requested');
            $table->unsignedInteger('quantity_approved')->nullable();
            $table->foreignId('source_drug_id')->nullable()->constrained('drugs')->nullOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected', 'shipped', 'received', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_orders');
    }
};
