<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Drug extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'medicine_id',
        'drug_name',
        'description',
        'dosage',
        'dosage_form',
        'batch_number',
        'expiry_date',
        'quantity_received',
        'quantity_on_hand',
        'reorder_point',
        'unit',
        'supplier',
        'cost_per_unit',
        'storage_location',
        'level',
        'status',
        'received_date',
        'last_issued_date',
        'notes',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expiry_date' => 'date',
        'received_date' => 'datetime',
        'last_issued_date' => 'datetime',
        'cost_per_unit' => 'decimal:2',
    ];

    /**
     * Get the user who created this drug.
     */
    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this drug.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the stock transfers for this drug.
     */
    public function stockTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class);
    }

    /**
     * Get the dispensing records for this drug.
     */
    public function dispensingRecords(): HasMany
    {
        return $this->hasMany(DispensingRecord::class);
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    /**
     * Get procurement orders for this drug.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Scope to filter drugs by level in supply chain.
     */
    public function scopeAtLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope to get drugs expiring within 6 months.
     */
    public function scopeExpiring($query)
    {
        return $query->where('expiry_date', '<=', Carbon::now()->addMonths(6))
                     ->where('expiry_date', '>', Carbon::now());
    }

    /**
     * Scope to get drugs with low stock.
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity_on_hand', '<=', 'reorder_point');
    }

    /**
     * Scope to get expired drugs.
     */
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', Carbon::now());
    }

    /**
     * Scope to exclude written-off drugs from active inventory.
     */
    public function scopeInInventory($query)
    {
        return $query->where('status', '!=', 'written_off');
    }

    /**
     * Scope to get active drugs only.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to search by drug name.
     */
    public function scopeByDrugName($query, string $name)
    {
        return $query->where('drug_name', 'like', "%{$name}%");
    }

    /**
     * Scope to search by batch number.
     */
    public function scopeByBatch($query, string $batch)
    {
        return $query->where('batch_number', 'like', "%{$batch}%");
    }

    /**
     * Calculate days since the drug was received.
     */
    public function getDaysInStorageAttribute(): int
    {
        if (!$this->received_date) {
            return 0;
        }
        return Carbon::parse($this->received_date)->diffInDays(Carbon::now());
    }

    /**
     * Calculate days until expiry (negative if expired).
     */
    public function getDaysUntilExpiryAttribute(): int
    {
        return Carbon::now()->diffInDays($this->expiry_date, false);
    }

    /**
     * Check if the drug is expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date < Carbon::now();
    }

    /**
     * Check if the drug is expiring soon (within 6 months).
     */
    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->expiry_date <= Carbon::now()->addMonths(6) && $this->expiry_date > Carbon::now();
    }

    /**
     * Check if the drug has low stock.
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity_on_hand <= $this->reorder_point;
    }

    /**
     * Get the status badge for display.
     */
    public function getStatusBadgeAttribute(): string
    {
        if ($this->is_expired) {
            return 'expired';
        }
        if ($this->is_expiring_soon) {
            return 'expiring_soon';
        }
        if ($this->is_low_stock) {
            return 'low_stock';
        }
        return 'active';
    }

    /**
     * Get the available quantity (alias for quantity_on_hand).
     */
    public function getAvailableQuantityAttribute(): int
    {
        return $this->quantity_on_hand;
    }

    /**
     * Update the quantity on hand and record the last issued date.
     */
    public function updateQuantity(int $quantity, string $reason = ''): void
    {
        $this->quantity_on_hand = $quantity;
        $this->last_issued_date = Carbon::now();
        $this->save();
    }

    /**
     * Check if the drug can be dispensed (not expired and has quantity).
     */
    public function canBeDispensed(): bool
    {
        return !$this->is_expired && $this->quantity_on_hand > 0;
    }

    /**
     * Format the expiry date for display.
     */
    public function formatExpiry(): string
    {
        return $this->expiry_date->format('M d, Y');
    }

    /**
     * JSON payload encoded into this batch's QR label.
     *
     * In MediTrack, each `drugs` row is one inventory batch (there is no
     * separate batches table). `drug_id` is therefore this batch's primary key.
     *
     * @return array{drug_id: int, batch_no: string, expiry: string}
     */
    public function qrPayload(): array
    {
        return [
            'drug_id' => $this->id,
            'batch_no' => (string) $this->batch_number,
            'expiry' => $this->expiry_date->format('Y-m-d'),
        ];
    }

    /**
     * Compact JSON string written into the QR code.
     */
    public function qrPayloadJson(): string
    {
        return json_encode($this->qrPayload(), JSON_UNESCAPED_SLASHES);
    }

    /**
     * Format the received date for display.
     */
    public function formatReceivedDate(): string
    {
        if (!$this->received_date) {
            return 'N/A';
        }
        return $this->received_date->format('M d, Y');
    }
}
