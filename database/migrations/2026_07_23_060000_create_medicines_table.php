<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('dosage');
            $table->enum('dosage_form', ['tablet', 'injection', 'syrup', 'cream', 'ointment', 'other']);
            $table->string('unit');
            $table->text('description')->nullable();
            $table->unsignedInteger('reorder_point')->default(100);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['name', 'dosage', 'dosage_form']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
