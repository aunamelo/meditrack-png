<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ward extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'ward_number',
        'name',
        'type',
        'location',
        'contact_person',
        'contact_phone',
        'bed_capacity',
        'is_active',
        'created_by',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'bed_capacity' => 'integer',
    ];

    public static function generateWardNumber(): string
    {
        $year = now()->year;
        $prefix = "WRD-{$year}-";

        $last = static::where('ward_number', 'like', "{$prefix}%")
            ->orderByDesc('ward_number')
            ->first();

        $nextSequence = $last ? ((int) substr($last->ward_number, -3)) + 1 : 1;

        return $prefix.str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function wardStocks(): HasMany
    {
        return $this->hasMany(WardStock::class);
    }

    public function drugIssuances(): HasMany
    {
        return $this->hasMany(DrugIssuance::class);
    }

    public function drugUsages(): HasMany
    {
        return $this->hasMany(DrugUsage::class);
    }

    public function drugReturns(): HasMany
    {
        return $this->hasMany(DrugReturn::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'general' => 'General Ward',
            'icu' => 'Intensive Care Unit',
            'pediatric' => 'Pediatric Ward',
            'maternity' => 'Maternity Ward',
            'surgical' => 'Surgical Ward',
            'emergency' => 'Emergency Ward',
            'outpatient' => 'Outpatient Clinic',
            default => ucfirst($this->type),
        };
    }
}
