<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'registration',
        'type',
        'depot',
        'is_active',
        'notes',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function stockTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAtDepot($query, string $depot)
    {
        return $query->where('depot', $depot);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'truck' => 'Truck',
            'van' => 'Van',
            'ute' => 'Ute',
            default => ucfirst($this->type),
        };
    }

    public function displayLabel(): string
    {
        return "{$this->name} ({$this->registration})";
    }
}
