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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->decimal('last_latitude', 10, 7)->nullable()->after('notes');
            $table->decimal('last_longitude', 10, 7)->nullable()->after('last_latitude');
            $table->decimal('last_speed_kmh', 6, 2)->nullable()->after('last_longitude');
            $table->timestamp('last_ping_at')->nullable()->after('last_speed_kmh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['last_latitude', 'last_longitude', 'last_speed_kmh', 'last_ping_at']);
        });
    }
};
