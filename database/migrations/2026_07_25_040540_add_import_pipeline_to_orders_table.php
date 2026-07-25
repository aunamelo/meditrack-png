<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('orders')->where('status', 'ordered')->update(['status' => 'manufacturing']);

        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'manufacturing', 'shipped', 'customs', 'fx_cleared', 'received', 'partial', 'cancelled') NOT NULL DEFAULT 'pending'");

        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('manufacturing_started_at')->nullable()->after('approved_at');
            $table->timestamp('shipped_at')->nullable()->after('manufacturing_started_at');
            $table->timestamp('customs_cleared_at')->nullable()->after('shipped_at');
            $table->timestamp('fx_cleared_at')->nullable()->after('customs_cleared_at');
        });

        DB::table('orders')
            ->where('status', 'manufacturing')
            ->whereNull('manufacturing_started_at')
            ->update(['manufacturing_started_at' => DB::raw('COALESCE(approved_at, created_at)')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('orders')->where('status', 'manufacturing')->update(['status' => 'ordered']);
        DB::table('orders')->whereIn('status', ['customs', 'fx_cleared'])->update(['status' => 'shipped']);

        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'ordered', 'shipped', 'received', 'partial', 'cancelled') NOT NULL DEFAULT 'pending'");

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'manufacturing_started_at',
                'shipped_at',
                'customs_cleared_at',
                'fx_cleared_at',
            ]);
        });
    }
};
