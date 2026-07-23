<?php

use App\Models\Drug;
use App\Models\Medicine;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('medicine_id')->nullable()->after('order_id')->constrained('medicines')->nullOnDelete();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('medicine_id')->nullable()->after('order_number')->constrained('medicines')->nullOnDelete();
        });

        Schema::table('drugs', function (Blueprint $table) {
            $table->foreignId('medicine_id')->nullable()->after('id')->constrained('medicines')->nullOnDelete();
        });

        $this->migrateExistingOrderItems();

        $this->makeForeignColumnNullable('order_items', 'drug_id');
        $this->makeForeignColumnNullable('orders', 'drug_id');
    }

    public function down(): void
    {
        Schema::table('drugs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('medicine_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('medicine_id');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('medicine_id');
        });
    }

    private function makeForeignColumnNullable(string $tableName, string $column): void
    {
        Schema::table($tableName, function (Blueprint $table) use ($column) {
            $table->dropForeign([$column]);
        });

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            Schema::table($tableName, function (Blueprint $table) use ($column) {
                $table->unsignedBigInteger($column)->nullable()->change();
            });
        } else {
            DB::statement("ALTER TABLE {$tableName} MODIFY {$column} BIGINT UNSIGNED NULL");
        }

        Schema::table($tableName, function (Blueprint $table) use ($column) {
            $table->foreign($column)->references('id')->on('drugs')->nullOnDelete();
        });
    }

    private function migrateExistingOrderItems(): void
    {
        if (! Schema::hasTable('order_items')) {
            return;
        }

        $systemUserId = DB::table('users')->orderBy('id')->value('id');

        if (! $systemUserId) {
            return;
        }

        DB::table('order_items')->orderBy('id')->chunkById(100, function ($items) use ($systemUserId) {
            foreach ($items as $item) {
                if (! $item->drug_id) {
                    continue;
                }

                $drug = Drug::find($item->drug_id);

                if (! $drug) {
                    continue;
                }

                $medicineId = $this->findOrCreateMedicineFromDrug($drug, $systemUserId);

                DB::table('order_items')->where('id', $item->id)->update([
                    'medicine_id' => $medicineId,
                ]);
            }
        });

        DB::table('orders')->orderBy('id')->chunkById(100, function ($orders) {
            foreach ($orders as $order) {
                $firstItem = DB::table('order_items')
                    ->where('order_id', $order->id)
                    ->orderBy('id')
                    ->first();

                if ($firstItem?->medicine_id) {
                    DB::table('orders')->where('id', $order->id)->update([
                        'medicine_id' => $firstItem->medicine_id,
                    ]);
                } elseif ($order->drug_id) {
                    $drug = Drug::find($order->drug_id);

                    if ($drug) {
                        $medicineId = DB::table('medicines')
                            ->where('name', $drug->drug_name)
                            ->where('dosage', $drug->dosage)
                            ->where('dosage_form', $drug->dosage_form)
                            ->value('id');

                        if ($medicineId) {
                            DB::table('orders')->where('id', $order->id)->update([
                                'medicine_id' => $medicineId,
                            ]);
                        }
                    }
                }
            }
        });
    }

    private function findOrCreateMedicineFromDrug(Drug $drug, int $userId): int
    {
        $existing = Medicine::query()
            ->where('name', $drug->drug_name)
            ->where('dosage', $drug->dosage)
            ->where('dosage_form', $drug->dosage_form)
            ->value('id');

        if ($existing) {
            if (! $drug->medicine_id) {
                $drug->update(['medicine_id' => $existing]);
            }

            return $existing;
        }

        $medicine = Medicine::create([
            'name' => $drug->drug_name,
            'dosage' => $drug->dosage,
            'dosage_form' => $drug->dosage_form,
            'unit' => $drug->unit,
            'description' => $drug->description,
            'reorder_point' => $drug->reorder_point,
            'is_active' => true,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        if (! $drug->medicine_id) {
            $drug->update(['medicine_id' => $medicine->id]);
        }

        return $medicine->id;
    }
};
