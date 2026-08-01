<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'country',
        'headquarters',
        'is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForOverseas($query)
    {
        return $query->whereIn('country', ['india', 'china']);
    }

    public function scopeForSource($query, string $source)
    {
        return match ($source) {
            'overseas' => $query->whereIn('country', ['india', 'china']),
            'local' => $query->where('country', 'png'),
            'donation' => $query->where('country', 'international'),
            default => $query,
        };
    }

    public function countryLabel(): string
    {
        return match ($this->country) {
            'india' => 'India',
            'china' => 'China',
            'png' => 'Papua New Guinea',
            'international' => 'International (WHO)',
            default => ucfirst($this->country),
        };
    }

    /**
     * Default quote currency for this supplier's country.
     */
    public function procurementCurrency(): string
    {
        return match ($this->country) {
            'india' => 'INR',
            'china' => 'CNY',
            'png' => 'PGK',
            default => 'USD',
        };
    }

    public function displayLabel(): string
    {
        return "{$this->name} ({$this->countryLabel()})";
    }
}
