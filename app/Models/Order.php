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
        'medicine_id',
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
        'manufacturing_started_at',
        'shipped_at',
        'customs_cleared_at',
        'fx_cleared_at',
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
        'manufacturing_started_at' => 'datetime',
        'shipped_at' => 'datetime',
        'customs_cleared_at' => 'datetime',
        'fx_cleared_at' => 'datetime',
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

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
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
        $this->loadMissing(['items.medicine', 'items.drug', 'medicine']);

        if ($this->items->isEmpty()) {
            return $this->medicine?->name ?? $this->drug?->drug_name ?? 'N/A';
        }

        $firstItem = $this->items->first();
        $first = $firstItem->medicine?->name ?? $firstItem->drug?->drug_name ?? 'Unknown medicine';
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
            'medicine_id' => $firstItem?->medicine_id,
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
        return $query->where('status', 'manufacturing');
    }

    public function scopeInPipeline($query)
    {
        return $query->whereIn('status', ['manufacturing', 'shipped', 'customs', 'fx_cleared']);
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
            'manufacturing' => 'blue',
            'shipped' => 'purple',
            'customs' => 'amber',
            'fx_cleared' => 'teal',
            'received' => 'green',
            'partial' => 'yellow',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pending approval',
            'manufacturing' => 'Manufacturing',
            'shipped' => 'In transit',
            'customs' => 'Customs clearance',
            'fx_cleared' => 'FX cleared',
            'received' => 'Received',
            'partial' => 'Partial delivery',
            'cancelled' => 'Cancelled',
            default => ucfirst((string) $this->status),
        };
    }

    /**
     * @return array<int, array{key: string, label: string, date: \Carbon\Carbon|null, completed: bool, current: bool}>
     */
    public function pipelineStages(): array
    {
        $stages = [
            ['key' => 'pending', 'label' => 'Awaiting approval'],
            ['key' => 'manufacturing', 'label' => 'Manufacturing'],
            ['key' => 'shipped', 'label' => 'International shipping'],
        ];

        if ($this->requiresImportClearance()) {
            $stages[] = ['key' => 'customs', 'label' => 'Customs clearance'];
            $stages[] = ['key' => 'fx_cleared', 'label' => 'FX cleared'];
        }

        $stages[] = ['key' => 'received', 'label' => 'Received at NDoH'];

        $currentIndex = $this->pipelineStageIndex();

        return collect($stages)->values()->map(function (array $stage, int $index) use ($currentIndex) {
            return [
                'key' => $stage['key'],
                'label' => $stage['label'],
                'date' => $this->pipelineStageDate($stage['key']),
                'completed' => $index < $currentIndex,
                'current' => $index === $currentIndex,
            ];
        })->all();
    }

    public function requiresImportClearance(): bool
    {
        return $this->source === 'overseas';
    }

    public function pipelineStageIndex(): int
    {
        if ($this->status === 'cancelled') {
            return 0;
        }

        if (in_array($this->status, ['received', 'partial'], true)) {
            return $this->requiresImportClearance() ? 5 : 3;
        }

        return match ($this->status) {
            'pending' => 0,
            'manufacturing' => 1,
            'shipped' => 2,
            'customs' => 3,
            'fx_cleared' => 4,
            default => 0,
        };
    }

    public function pipelineStageDate(string $stageKey): ?Carbon
    {
        return match ($stageKey) {
            'pending' => $this->created_at,
            'manufacturing' => $this->manufacturing_started_at ?? $this->approved_at,
            'shipped' => $this->shipped_at,
            'customs' => $this->customs_cleared_at,
            'fx_cleared' => $this->fx_cleared_at,
            'received' => $this->actual_delivery_date ?? $this->received_at,
            default => null,
        };
    }

    public function pipelineProgressPercentage(): int
    {
        $stages = $this->pipelineStages();
        $total = max(count($stages) - 1, 1);
        $current = $this->pipelineStageIndex();

        if (in_array($this->status, ['received', 'partial'], true)) {
            return 100;
        }

        return (int) round(($current / $total) * 100);
    }

    public function canAdvancePipeline(): bool
    {
        if ($this->status === 'manufacturing') {
            return true;
        }

        if ($this->status === 'shipped' && $this->requiresImportClearance()) {
            return true;
        }

        return $this->status === 'customs';
    }

    public function nextPipelineStatus(): ?string
    {
        return match ($this->status) {
            'manufacturing' => 'shipped',
            'shipped' => $this->requiresImportClearance() ? 'customs' : null,
            'customs' => 'fx_cleared',
            default => null,
        };
    }

    public function nextPipelineActionLabel(): ?string
    {
        return match ($this->nextPipelineStatus()) {
            'shipped' => 'Mark as shipped',
            'customs' => 'Mark customs cleared',
            'fx_cleared' => 'Mark FX cleared',
            default => null,
        };
    }

    public function advancePipeline(?string $notes = null): void
    {
        $nextStatus = $this->nextPipelineStatus();

        if (! $nextStatus) {
            return;
        }

        $updates = ['status' => $nextStatus];

        match ($nextStatus) {
            'shipped' => $updates['shipped_at'] = now(),
            'customs' => $updates['customs_cleared_at'] = now(),
            'fx_cleared' => $updates['fx_cleared_at'] = now(),
            default => null,
        };

        if ($notes) {
            $updates['notes'] = trim(($this->notes ?? '')."\n\nPipeline update ({$this->statusLabel()} → ".ucfirst(str_replace('_', ' ', $nextStatus))."): ".$notes);
        }

        $this->update($updates);
    }

    /**
     * @return array<string, int>
     */
    public static function pipelineCounts(?int $userId = null): array
    {
        $query = static::query()->inPipeline();

        if ($userId) {
            $query->where('created_by', $userId);
        }

        return [
            'total' => (clone $query)->count(),
            'manufacturing' => (clone $query)->where('status', 'manufacturing')->count(),
            'shipped' => (clone $query)->where('status', 'shipped')->count(),
            'customs' => (clone $query)->where('status', 'customs')->count(),
            'fx_cleared' => (clone $query)->where('status', 'fx_cleared')->count(),
        ];
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
        if ($this->status === 'partial') {
            return true;
        }

        if ($this->requiresImportClearance()) {
            return $this->status === 'fx_cleared';
        }

        return $this->status === 'shipped';
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
            'manufacturing_started_at' => now(),
            'status' => 'manufacturing',
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
