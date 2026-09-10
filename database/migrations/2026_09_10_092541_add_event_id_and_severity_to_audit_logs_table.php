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
        // Add columns without unique constraint first
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('event_id', 64)->nullable()->after('id');
            $table->string('severity', 20)->default('INFO')->after('description');
            $table->string('session_id', 255)->nullable()->after('ip_address');
            $table->string('request_method', 10)->nullable()->after('session_id');
            $table->string('request_path', 500)->nullable()->after('request_method');

            $table->index('severity');
            $table->index('ip_address');
        });

        // Populate existing records with unique event IDs
        \DB::statement('UPDATE audit_logs SET event_id = UUID() WHERE event_id IS NULL');

        // Now add unique constraint
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->unique('event_id');
            $table->index('event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropUnique(['event_id']);
            $table->dropIndex(['severity']);
            $table->dropIndex(['ip_address']);
            $table->dropIndex(['event_id']);
            $table->dropColumn(['event_id', 'severity', 'session_id', 'request_method', 'request_path']);
        });
    }
};
