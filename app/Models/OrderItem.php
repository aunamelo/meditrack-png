<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'drug_id',
        'quantity_ordered',
        'quantity_received',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function drug(): BelongsTo
    {
        return $this->belongsTo(Drug::class);
    }

    public function remainingQuantity(): int
    {
        return max(0, $this->quantity_ordered - ($this->quantity_received ?? 0));
    }

    public function isFullyReceived(): bool
    {
        return $this->remainingQuantity() === 0;
    }

    public function receiveQuantity(int $quantity): void
    {
        $this->update([
            'quantity_received' => ($this->quantity_received ?? 0) + $quantity,
        ]);
    }

    public function getProgressPercentage(): float
    {
        if ($this->quantity_ordered <= 0) {
            return 0;
        }

        return min(100, round((($this->quantity_received ?? 0) / $this->quantity_ordered) * 100, 1));
    }
}
