<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dispensing_records', function (Blueprint $table) {
            $table->date('prescription_date')->nullable()->after('prescription_ref');
            $table->string('prescriber_name')->nullable()->after('prescription_date');
            $table->string('prescribed_dose')->nullable()->after('prescriber_name');
            $table->boolean('audit_date_checked')->default(false)->after('prescribed_dose');
            $table->boolean('audit_prescriber_checked')->default(false)->after('audit_date_checked');
            $table->boolean('audit_drug_dose_checked')->default(false)->after('audit_prescriber_checked');
            $table->boolean('audit_contraindications_checked')->default(false)->after('audit_drug_dose_checked');
        });
    }

    public function down(): void
    {
        Schema::table('dispensing_records', function (Blueprint $table) {
            $table->dropColumn([
                'prescription_date',
                'prescriber_name',
                'prescribed_dose',
                'audit_date_checked',
                'audit_prescriber_checked',
                'audit_drug_dose_checked',
                'audit_contraindications_checked',
            ]);
        });
    }
};
