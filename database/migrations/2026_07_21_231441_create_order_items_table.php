<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('drug_id')->constrained('drugs');
            $table->unsignedInteger('quantity_ordered');
            $table->unsignedInteger('quantity_received')->default(0);
            $table->timestamps();
        });

        if (Schema::hasTable('orders')) {
            $orders = DB::table('orders')->whereNotNull('drug_id')->get();

            foreach ($orders as $order) {
                DB::table('order_items')->insert([
                    'order_id' => $order->id,
                    'drug_id' => $order->drug_id,
                    'quantity_ordered' => $order->quantity_ordered,
                    'quantity_received' => $order->quantity_received ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
