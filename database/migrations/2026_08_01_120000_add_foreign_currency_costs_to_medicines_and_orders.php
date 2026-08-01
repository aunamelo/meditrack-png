<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->decimal('unit_cost', 12, 4)->nullable()->after('reorder_point');
            $table->string('currency', 3)->nullable()->after('unit_cost');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('invoice_amount_foreign', 14, 2)->nullable()->after('invoice_amount');
            $table->string('invoice_currency', 3)->nullable()->after('invoice_amount_foreign');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['invoice_amount_foreign', 'invoice_currency']);
        });

        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn(['unit_cost', 'currency']);
        });
    }
};
