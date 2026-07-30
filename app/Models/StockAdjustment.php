<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'adjustment_number',
        'drug_id',
        'level',
        'quantity_system',
        'quantity_counted',
        'quantity_delta',
        'reason',
        'notes',
        'adjusted_by',
        'adjusted_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'adjusted_at' => 'datetime',
    ];

    public static function generateAdjustmentNumber(): string
    {
        $year = now()->year;
        $prefix = "ADJ-{$year}-";

        $last = static::where('adjustment_number', 'like', "{$prefix}%")
            ->orderByDesc('adjustment_number')
            ->first();

        $nextSequence = $last ? ((int) substr($last->adjustment_number, -3)) + 1 : 1;

        return $prefix.str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT);
    }

    public function drug(): BelongsTo
    {
        return $this->belongsTo(Drug::class);
    }

    public function adjuster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    public function reasonLabel(): string
    {
        return match ($this->reason) {
            'physical_count' => 'Physical stock take',
            'damaged' => 'Damaged',
            'expired' => 'Expired / write-off',
            'theft_loss' => 'Theft / loss',
            'found_stock' => 'Found stock',
            'correction' => 'Data correction',
            default => 'Other',
        };
    }

    public function scopeAtLevel($query, string $level)
    {
        return $query->where('level', $level);
    }
}
