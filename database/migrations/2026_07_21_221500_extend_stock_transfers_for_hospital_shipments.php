<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->foreignId('hospital_order_id')->nullable()->after('destination_drug_id')->constrained('hospital_orders')->nullOnDelete();
        });

        Schema::table('hospital_orders', function (Blueprint $table) {
            $table->foreignId('stock_transfer_id')->nullable()->after('reviewed_at')->constrained('stock_transfers')->nullOnDelete();
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->expandSqliteTransferLevels();
        } else {
            Schema::table('stock_transfers', function (Blueprint $table) {
                $table->string('from_level', 20)->default('ndoh')->change();
                $table->string('to_level', 20)->default('lae_ams')->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('hospital_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('stock_transfer_id');
        });

        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('hospital_order_id');
        });
    }

    protected function expandSqliteTransferLevels(): void
    {
        DB::statement('PRAGMA foreign_keys=OFF');

        Schema::rename('stock_transfers', 'stock_transfers_old');

        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->foreignId('drug_id')->constrained('drugs');
            $table->foreignId('destination_drug_id')->nullable()->constrained('drugs');
            $table->foreignId('hospital_order_id')->nullable()->constrained('hospital_orders')->nullOnDelete();
            $table->string('batch_number');
            $table->integer('quantity_sent');
            $table->string('from_level', 20)->default('ndoh');
            $table->string('to_level', 20)->default('lae_ams');
            $table->date('sent_date');
            $table->enum('status', ['sent', 'received', 'cancelled'])->default('sent');
            $table->text('notes')->nullable();
            $table->foreignId('sent_by')->constrained('users');
            $table->foreignId('received_by')->nullable()->constrained('users');
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });

        DB::statement('
            INSERT INTO stock_transfers (
                id, transfer_number, drug_id, destination_drug_id, hospital_order_id,
                batch_number, quantity_sent, from_level, to_level, sent_date, status,
                notes, sent_by, received_by, received_at, created_at, updated_at
            )
            SELECT
                id, transfer_number, drug_id, destination_drug_id, NULL,
                batch_number, quantity_sent, from_level, to_level, sent_date, status,
                notes, sent_by, received_by, received_at, created_at, updated_at
            FROM stock_transfers_old
        ');

        Schema::drop('stock_transfers_old');

        DB::statement('PRAGMA foreign_keys=ON');
    }
};
