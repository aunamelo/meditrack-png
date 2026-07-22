<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'order_number',
        'drug_id',
        'quantity_ordered',
        'quantity_received',
        'supplier',
        'order_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'supplier_invoice',
        'invoice_amount',
        'status',
        'source',
        'notes',
        'created_by',
        'approved_by',
        'received_by',
        'approved_at',
        'received_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'invoice_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    /**
     * Generate the next unique order number for the current year (ORD-YYYY-001).
     */
    public static function generateOrderNumber(): string
    {
        $year = now()->year;
        $prefix = "ORD-{$year}-";

        $lastOrder = static::withTrashed()
            ->where('order_number', 'like', "{$prefix}%")
            ->orderByDesc('order_number')
            ->first();

        if ($lastOrder) {
            $lastSequence = (int) substr($lastOrder->order_number, -3);
            $nextSequence = $lastSequence + 1;
        } else {
            $nextSequence = 1;
        }

        return $prefix.str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT);
    }

    public function drug(): BelongsTo
    {
        return $this->belongsTo(Drug::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class)->orderBy('id');
    }

    /**
     * Summary label for lists — first drug name plus count when multi-line.
     */
    public function itemsSummary(): string
    {
        $this->loadMissing('items.drug');

        if ($this->items->isEmpty()) {
            return $this->drug?->drug_name ?? 'N/A';
        }

        $first = $this->items->first()->drug?->drug_name ?? 'Unknown drug';
        $extra = $this->items->count() - 1;

        return $extra > 0 ? "{$first} +{$extra} more" : $first;
    }

    public function hasMultipleItems(): bool
    {
        if ($this->relationLoaded('items')) {
            return $this->items->count() > 1;
        }

        return $this->items()->count() > 1;
    }

    public function syncLegacyColumnsFromItems(): void
    {
        $firstItem = $this->items()->orderBy('id')->first();

        $this->update([
            'drug_id' => $firstItem?->drug_id,
            'quantity_ordered' => (int) $this->items()->sum('quantity_ordered'),
            'quantity_received' => (int) $this->items()->sum('quantity_received'),
        ]);
    }

    public function syncStatusFromItems(): void
    {
        $items = $this->items()->get();

        if ($items->isEmpty()) {
            return;
        }

        $allReceived = $items->every(fn (OrderItem $item) => $item->isFullyReceived());
        $anyReceived = $items->contains(fn (OrderItem $item) => ($item->quantity_received ?? 0) > 0);

        if ($allReceived) {
            $this->update(['status' => 'received']);
        } elseif ($anyReceived) {
            $this->update(['status' => 'partial']);
        }
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOrdered($query)
    {
        return $query->where('status', 'ordered');
    }

    public function scopeShipped($query)
    {
        return $query->where('status', 'shipped');
    }

    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    public function scopePartial($query)
    {
        return $query->where('status', 'partial');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeByDrug($query, int $drugId)
    {
        return $query->where('drug_id', $drugId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCreatedBy($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotNull('expected_delivery_date')
            ->where('expected_delivery_date', '<', Carbon::today())
            ->whereNotIn('status', ['received', 'cancelled']);
    }

    public function daysOverdue(): int
    {
        if (! $this->expected_delivery_date || ! $this->isOverdue()) {
            return 0;
        }

        return Carbon::parse($this->expected_delivery_date)->diffInDays(Carbon::today());
    }

    public function isOverdue(): bool
    {
        return $this->expected_delivery_date
            && $this->expected_delivery_date->lt(Carbon::today())
            && ! in_array($this->status, ['received', 'cancelled'], true);
    }

    public function isReceived(): bool
    {
        return $this->status === 'received';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function getStatusBadge(): string
    {
        return match ($this->status) {
            'pending' => 'gray',
            'ordered' => 'blue',
            'shipped' => 'purple',
            'received' => 'green',
            'partial' => 'yellow',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    public function getProgressPercentage(): float
    {
        $this->loadMissing('items');

        if ($this->items->isNotEmpty()) {
            $ordered = $this->items->sum('quantity_ordered');
            $received = $this->items->sum('quantity_received');

            if ($ordered <= 0) {
                return 0;
            }

            return min(100, round(($received / $ordered) * 100, 1));
        }

        if ($this->quantity_ordered <= 0) {
            return 0;
        }

        return min(100, round((($this->quantity_received ?? 0) / $this->quantity_ordered) * 100, 1));
    }

    public function getReceiptPercentage(): float
    {
        return $this->getProgressPercentage();
    }

    public function canApprove(): bool
    {
        return $this->status === 'pending';
    }

    public function canReceive(): bool
    {
        return in_array($this->status, ['ordered', 'shipped', 'partial'], true);
    }

    public function formatOrderDate(): string
    {
        return $this->order_date->format('M d, Y');
    }

    public function formatDeliveryDate(): string
    {
        if (! $this->expected_delivery_date) {
            return 'N/A';
        }

        return $this->expected_delivery_date->format('M d, Y');
    }

    /**
     * Approve a pending order — moves it to ordered status.
     */
    public function approve(int $userId): void
    {
        $this->update([
            'approved_by' => $userId,
            'approved_at' => now(),
            'status' => 'ordered',
        ]);
    }

    /**
     * Record receipt of goods — updates line items and order status.
     *
     * @param  array<int, int>  $quantitiesByItemId  order_item_id => quantity received
     */
    public function receiveItems(array $quantitiesByItemId, int $userId, ?Carbon $receivedDate = null): void
    {
        $receivedDate ??= Carbon::today();

        foreach ($quantitiesByItemId as $itemId => $quantityReceived) {
            $quantityReceived = (int) $quantityReceived;

            if ($quantityReceived <= 0) {
                continue;
            }

            /** @var OrderItem $item */
            $item = $this->items()->findOrFail($itemId);
            $item->receiveQuantity($quantityReceived);
        }

        $this->syncLegacyColumnsFromItems();
        $this->syncStatusFromItems();

        $this->update([
            'received_by' => $userId,
            'received_at' => now(),
            'actual_delivery_date' => $receivedDate,
        ]);
    }

    /**
     * Record receipt of goods — single-line legacy helper.
     */
    public function receive(int $quantityReceived, int $userId, ?Carbon $receivedDate = null): void
    {
        $receivedDate ??= Carbon::today();

        $this->loadMissing('items');

        if ($this->items->isNotEmpty()) {
            $item = $this->items->first();
            $this->receiveItems([$item->id => $quantityReceived], $userId, $receivedDate);

            return;
        }

        $totalReceived = ($this->quantity_received ?? 0) + $quantityReceived;
        $status = $totalReceived >= ($this->quantity_ordered ?? 0) ? 'received' : 'partial';

        $this->update([
            'quantity_received' => $totalReceived,
            'received_by' => $userId,
            'received_at' => now(),
            'actual_delivery_date' => $receivedDate,
            'status' => $status,
        ]);
    }

    /**
     * Cancel an order and append the reason to notes.
     */
    public function cancel(string $reason): void
    {
        $notes = trim(($this->notes ?? '')."\n\nCancelled: ".$reason);

        $this->update([
            'status' => 'cancelled',
            'notes' => $notes,
        ]);
    }
}
