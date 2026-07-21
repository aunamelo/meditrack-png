<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierQuote extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'drug_name',
        'dosage',
        'supplier_name',
        'country',
        'unit_price',
        'quote_currency',
        'min_order_qty',
        'lead_time_days',
        'source',
        'is_active',
        'notes',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'unit_price' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForDrugType($query, string $drugName, string $dosage)
    {
        return $query->where('drug_name', $drugName)->where('dosage', $dosage);
    }
}
