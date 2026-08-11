<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_order_id')->constrained('hospital_orders')->cascadeOnDelete();
            $table->string('drug_name');
            $table->string('dosage', 100);
            $table->unsignedInteger('quantity_requested');
            $table->unsignedInteger('quantity_approved')->nullable();
            $table->foreignId('source_drug_id')->nullable()->constrained('drugs')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->cascadeOnDelete();
            $table->foreignId('hospital_order_item_id')->nullable()->constrained('hospital_order_items')->nullOnDelete();
            $table->foreignId('drug_id')->constrained('drugs')->restrictOnDelete();
            $table->foreignId('destination_drug_id')->nullable()->constrained('drugs')->nullOnDelete();
            $table->string('batch_number');
            $table->unsignedInteger('quantity_sent');
            $table->unsignedInteger('quantity_received')->nullable();
            $table->timestamps();
        });

        // Backfill: each existing single-medicine hospital order becomes one line item.
        $orders = DB::table('hospital_orders')->orderBy('id')->get();
        foreach ($orders as $order) {
            if ($order->drug_name === null || $order->drug_name === '') {
                continue;
            }

            $itemId = DB::table('hospital_order_items')->insertGetId([
                'hospital_order_id' => $order->id,
                'drug_name' => $order->drug_name,
                'dosage' => $order->dosage ?? '',
                'quantity_requested' => (int) $order->quantity_requested,
                'quantity_approved' => $order->quantity_approved,
                'source_drug_id' => $order->source_drug_id,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ]);

            if ($order->stock_transfer_id) {
                $transfer = DB::table('stock_transfers')->where('id', $order->stock_transfer_id)->first();
                if ($transfer) {
                    DB::table('stock_transfer_items')->insert([
                        'stock_transfer_id' => $transfer->id,
                        'hospital_order_item_id' => $itemId,
                        'drug_id' => $transfer->drug_id,
                        'destination_drug_id' => $transfer->destination_drug_id,
                        'batch_number' => $transfer->batch_number,
                        'quantity_sent' => (int) $transfer->quantity_sent,
                        'quantity_received' => $transfer->status === 'received' ? (int) $transfer->quantity_sent : null,
                        'created_at' => $transfer->created_at,
                        'updated_at' => $transfer->updated_at,
                    ]);
                }
            }
        }

        // Keep header drug_name / quantity_* columns for older views/reports;
        // new code treats hospital_order_items as the source of truth and syncs a summary onto the header.
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('hospital_order_items');
    }
};
