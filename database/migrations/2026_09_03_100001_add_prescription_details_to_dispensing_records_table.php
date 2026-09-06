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
        Schema::table('dispensing_records', function (Blueprint $table) {
            // Prescription Information
            $table->string('diagnosis')->nullable();
            $table->string('dosage_form')->nullable();
            $table->string('strength')->nullable();
            $table->unsignedInteger('quantity_to_dispense')->nullable();
            $table->text('directions_for_use')->nullable();

            // Prescriber Details
            $table->string('prescriber_designation')->nullable();
            $table->string('prescriber_signature')->nullable();
            $table->boolean('hospital_stamp')->default(false);

            // Pharmacy / Dispensing Section
            $table->string('pharmacist_signature')->nullable();
            $table->text('dispensing_notes')->nullable();

            // Additional Notes
            $table->text('allergies_contraindications')->nullable();
            $table->text('repeat_instructions')->nullable();
            $table->text('follow_up_instructions')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dispensing_records', function (Blueprint $table) {
            // Prescription Information
            $table->dropColumn(['diagnosis', 'dosage_form', 'strength', 'quantity_to_dispense', 'directions_for_use']);

            // Prescriber Details
            $table->dropColumn(['prescriber_designation', 'prescriber_signature', 'hospital_stamp']);

            // Pharmacy / Dispensing Section
            $table->dropColumn(['pharmacist_signature', 'dispensing_notes']);

            // Additional Notes
            $table->dropColumn(['allergies_contraindications', 'repeat_instructions', 'follow_up_instructions']);
        });
    }
};
