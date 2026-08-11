<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HospitalOrder extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'order_number',
        'drug_name',
        'dosage',
        'quantity_requested',
        'quantity_approved',
        'source_drug_id',
        'status',
        'notes',
        'rejection_reason',
        'requested_by',
        'reviewed_by',
        'reviewed_at',
        'stock_transfer_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public static function generateOrderNumber(): string
    {
        $year = now()->year;
        $prefix = "HOR-{$year}-";

        $last = static::where('order_number', 'like', "{$prefix}%")
            ->orderByDesc('order_number')
            ->first();

        $nextSequence = $last ? ((int) substr($last->order_number, -3)) + 1 : 1;

        return $prefix.str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT);
    }

    public function items(): HasMany
    {
        return $this->hasMany(HospitalOrderItem::class);
    }

    public function sourceDrug(): BelongsTo
    {
        return $this->belongsTo(Drug::class, 'source_drug_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function stockTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class);
    }

    public function discrepancyReports(): HasMany
    {
        return $this->hasMany(DiscrepancyReport::class);
    }

    /**
     * Human-readable list of medicines on this request (one delivery).
     */
    public function medicinesLabel(): string
    {
        $items = $this->relationLoaded('items') ? $this->items : $this->items()->get();

        if ($items->isEmpty()) {
            return trim(($this->drug_name ?? '').($this->dosage ? ' ('.$this->dosage.')' : '')) ?: 'Hospital order';
        }

        if ($items->count() === 1) {
            return $items->first()->displayLabel();
        }

        return $items->first()->displayLabel().' + '.($items->count() - 1).' more';
    }

    public function totalQuantityRequested(): int
    {
        if ($this->relationLoaded('items') && $this->items->isNotEmpty()) {
            return (int) $this->items->sum('quantity_requested');
        }

        return (int) ($this->quantity_requested ?? 0);
    }

    public function totalQuantityApproved(): int
    {
        if ($this->relationLoaded('items') && $this->items->isNotEmpty()) {
            return (int) $this->items->sum('quantity_approved');
        }

        return (int) ($this->quantity_approved ?? 0);
    }

    /**
     * Sync header summary columns from line items (keeps index/search working).
     */
    public function syncHeaderFromItems(): void
    {
        $items = $this->items()->get();
        $first = $items->first();

        $this->update([
            'drug_name' => $first?->drug_name ?? $this->drug_name,
            'dosage' => $first?->dosage ?? $this->dosage,
            'quantity_requested' => (int) $items->sum('quantity_requested'),
            'quantity_approved' => $items->every(fn (HospitalOrderItem $item) => $item->quantity_approved !== null)
                ? (int) $items->sum('quantity_approved')
                : null,
            'source_drug_id' => $items->count() === 1 ? $first?->source_drug_id : null,
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function canApprove(): bool
    {
        return $this->status === 'pending';
    }

    public function canReject(): bool
    {
        return $this->status === 'pending';
    }

    public function canShip(): bool
    {
        if ($this->status !== 'approved') {
            return false;
        }

        $items = $this->relationLoaded('items') ? $this->items : $this->items()->get();

        if ($items->isNotEmpty()) {
            return $items->every(fn (HospitalOrderItem $item) => $item->source_drug_id && $item->quantity_approved);
        }

        return (bool) $this->source_drug_id;
    }

    public function canReceive(): bool
    {
        return $this->status === 'shipped';
    }

    public function getStatusBadge(): string
    {
        return match ($this->status) {
            'pending' => 'amber',
            'approved' => 'blue',
            'rejected' => 'red',
            'shipped' => 'purple',
            'received' => 'green',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }

    /**
     * @return array<int, array{key: string, label: string, date: \Carbon\Carbon|null, completed: bool, current: bool}>
     */
    public function pipelineStages(): array
    {
        if ($this->status === 'rejected') {
            return [
                [
                    'key' => 'pending',
                    'label' => 'Requested',
                    'date' => $this->created_at,
                    'completed' => true,
                    'current' => false,
                ],
                [
                    'key' => 'rejected',
                    'label' => 'Rejected',
                    'date' => $this->reviewed_at,
                    'completed' => false,
                    'current' => true,
                ],
            ];
        }

        $stages = [
            ['key' => 'pending', 'label' => 'Requested', 'date' => $this->created_at],
            ['key' => 'approved', 'label' => 'Approved at Lae AMS', 'date' => $this->status === 'pending' ? null : $this->reviewed_at],
            ['key' => 'shipped', 'label' => 'Dispatched by road', 'date' => $this->stockTransfer?->sent_date],
            ['key' => 'received', 'label' => 'Received at Modilon', 'date' => $this->stockTransfer?->received_at],
        ];

        $currentIndex = match ($this->status) {
            'pending' => 0,
            'approved' => 1,
            'shipped' => 2,
            'received' => 3,
            'cancelled' => 0,
            default => 0,
        };

        return collect($stages)->values()->map(function (array $stage, int $index) use ($currentIndex) {
            return [
                'key' => $stage['key'],
                'label' => $stage['label'],
                'date' => $stage['date'],
                'completed' => $this->status === 'received' ? true : $index < $currentIndex,
                'current' => $this->status === 'received' ? false : $index === $currentIndex,
            ];
        })->all();
    }

    public function pipelineProgressPercentage(): int
    {
        if ($this->status === 'received') {
            return 100;
        }

        if ($this->status === 'rejected') {
            return 100;
        }

        return match ($this->status) {
            'pending' => 0,
            'approved' => 33,
            'shipped' => 66,
            default => 0,
        };
    }
}
