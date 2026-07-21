<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discrepancy_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_number')->unique();
            $table->foreignId('hospital_order_id')->nullable()->constrained('hospital_orders')->nullOnDelete();
            $table->foreignId('stock_transfer_id')->nullable()->constrained('stock_transfers')->nullOnDelete();
            $table->enum('issue_type', ['short_shipment', 'damaged', 'wrong_item', 'expired', 'other'])->default('other');
            $table->unsignedInteger('quantity_expected')->nullable();
            $table->unsignedInteger('quantity_received')->nullable();
            $table->text('description');
            $table->enum('status', ['open', 'resolved'])->default('open');
            $table->text('resolution_notes')->nullable();
            $table->foreignId('reported_by')->constrained('users');
            $table->foreignId('resolved_by')->nullable()->constrained('users');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discrepancy_reports');
    }
};
