<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('supplier_quotes');
    }

    public function down(): void
    {
        // Supplier quote comparison was removed from the app; no restore path.
    }
};
