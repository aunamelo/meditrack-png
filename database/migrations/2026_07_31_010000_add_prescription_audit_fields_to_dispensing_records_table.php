<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'prescription_date' => fn (Blueprint $table) => $table->date('prescription_date')->nullable()->after('prescription_ref'),
            'prescriber_name' => fn (Blueprint $table) => $table->string('prescriber_name')->nullable()->after('prescription_date'),
            'prescribed_dose' => fn (Blueprint $table) => $table->string('prescribed_dose')->nullable()->after('prescriber_name'),
            'audit_date_checked' => fn (Blueprint $table) => $table->boolean('audit_date_checked')->default(false)->after('prescribed_dose'),
            'audit_prescriber_checked' => fn (Blueprint $table) => $table->boolean('audit_prescriber_checked')->default(false)->after('audit_date_checked'),
            'audit_drug_dose_checked' => fn (Blueprint $table) => $table->boolean('audit_drug_dose_checked')->default(false)->after('audit_prescriber_checked'),
            'audit_contraindications_checked' => fn (Blueprint $table) => $table->boolean('audit_contraindications_checked')->default(false)->after('audit_drug_dose_checked'),
        ];

        $missing = array_keys(array_filter(
            $columns,
            fn (string $column) => ! Schema::hasColumn('dispensing_records', $column),
            ARRAY_FILTER_USE_KEY
        ));

        if ($missing === []) {
            return;
        }

        Schema::table('dispensing_records', function (Blueprint $table) use ($columns, $missing) {
            foreach ($missing as $column) {
                $columns[$column]($table);
            }
        });
    }

    public function down(): void
    {
        $columns = [
            'prescription_date',
            'prescriber_name',
            'prescribed_dose',
            'audit_date_checked',
            'audit_prescriber_checked',
            'audit_drug_dose_checked',
            'audit_contraindications_checked',
        ];

        $existing = array_values(array_filter(
            $columns,
            fn (string $column) => Schema::hasColumn('dispensing_records', $column)
        ));

        if ($existing === []) {
            return;
        }

        Schema::table('dispensing_records', function (Blueprint $table) use ($existing) {
            $table->dropColumn($existing);
        });
    }
};
