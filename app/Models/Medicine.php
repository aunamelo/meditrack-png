<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'dosage',
        'dosage_form',
        'unit',
        'description',
        'supplier_id',
        'reorder_point',
        'unit_cost',
        'currency',
        'is_active',
        'created_by',
        'updated_by',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'reorder_point' => 'integer',
        'unit_cost' => 'decimal:4',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function inventoryBatches(): HasMany
    {
        return $this->hasMany(Drug::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($builder) use ($term) {
            $builder->where('name', 'like', "%{$term}%")
                ->orWhere('dosage', 'like', "%{$term}%");
        });
    }

    public function displayLabel(): string
    {
        return "{$this->name} ({$this->dosage})";
    }

    public function formLabel(): string
    {
        return ucfirst(str_replace('_', ' ', $this->dosage_form));
    }

    /**
     * Quote currency for this catalog entry (explicit, or inferred from supplier country).
     */
    public function quoteCurrency(): ?string
    {
        if ($this->currency) {
            return strtoupper($this->currency);
        }

        return $this->supplier?->procurementCurrency();
    }

    public function formatUnitCost(): ?string
    {
        if ($this->unit_cost === null) {
            return null;
        }

        $currency = $this->quoteCurrency() ?? 'PGK';
        $amount = number_format((float) $this->unit_cost, 4);

        return $currency === 'PGK'
            ? "K {$amount}"
            : "{$amount} {$currency}";
    }
}
