<?php

namespace App\Services;

use App\Models\Drug;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockAdjustmentService
{
    /**
     * Post a physical count / stock adjustment and update batch on-hand.
     *
     * @param  array{drug_id: int, quantity_counted: int, reason: string, notes?: string|null}  $data
     */
    public static function post(User $user, array $data, string $expectedLevel): StockAdjustment
    {
        return DB::transaction(function () use ($user, $data, $expectedLevel) {
            $drug = Drug::query()->lockForUpdate()->findOrFail($data['drug_id']);

            if ($drug->level !== $expectedLevel) {
                throw new InvalidArgumentException('You can only adjust stock at your facility level.');
            }

            if ($drug->status === 'written_off') {
                throw new InvalidArgumentException('Written-off batches cannot be adjusted.');
            }

            $systemQty = (int) $drug->quantity_on_hand;
            $counted = (int) $data['quantity_counted'];
            $delta = $counted - $systemQty;

            $adjustment = StockAdjustment::create([
                'adjustment_number' => StockAdjustment::generateAdjustmentNumber(),
                'drug_id' => $drug->id,
                'level' => $drug->level,
                'quantity_system' => $systemQty,
                'quantity_counted' => $counted,
                'quantity_delta' => $delta,
                'reason' => $data['reason'],
                'notes' => $data['notes'] ?? null,
                'adjusted_by' => $user->id,
                'adjusted_at' => now(),
            ]);

            $drug->quantity_on_hand = $counted;
            $drug->updated_by = $user->id;
            $drug->save();

            return $adjustment;
        });
    }
}
