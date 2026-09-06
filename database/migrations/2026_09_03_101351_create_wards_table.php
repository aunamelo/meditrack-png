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
        Schema::create('wards', function (Blueprint $table) {
            $table->id();
            $table->string('ward_number')->unique();
            $table->string('name');
            $table->string('type')->default('general'); // general, icu, pediatric, maternity, surgical, etc.
            $table->string('location')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->integer('bed_capacity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wards');
    }
};
