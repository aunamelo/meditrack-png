<?php

use App\Models\Drug;
use App\Models\StockTransfer;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        StockTransfer::query()
            ->where('to_level', 'modilon_hospital')
            ->where('status', 'sent')
            ->whereNotNull('destination_drug_id')
            ->each(function (StockTransfer $transfer): void {
                Drug::query()->whereKey($transfer->destination_drug_id)->delete();
                $transfer->update(['destination_drug_id' => null]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Premature inventory rows were removed; they cannot be restored automatically.
    }
};
