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
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->foreignId('drug_id')->constrained('drugs');
            $table->foreignId('destination_drug_id')->nullable()->constrained('drugs');
            $table->string('batch_number');
            $table->integer('quantity_sent');
            $table->enum('from_level', ['ndoh'])->default('ndoh');
            $table->enum('to_level', ['lae_ams'])->default('lae_ams');
            $table->date('sent_date');
            $table->enum('status', ['sent', 'received', 'cancelled'])->default('sent');
            $table->text('notes')->nullable();
            $table->foreignId('sent_by')->constrained('users');
            $table->foreignId('received_by')->nullable()->constrained('users');
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};
