<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrugUsage extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'ward_id',
        'drug_id',
        'patient_id',
        'quantity_used',
        'usage_type',
        'usage_date',
        'recorded_by',
        'notes',
        'created_by',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'quantity_used' => 'integer',
        'usage_date' => 'datetime',
    ];

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function drug(): BelongsTo
    {
        return $this->belongsTo(Drug::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function usageTypeLabel(): string
    {
        return match ($this->usage_type) {
            'administration' => 'Administration',
            'wastage' => 'Wastage',
            'loss' => 'Loss',
            'damage' => 'Damage',
            'expired' => 'Expired',
            'other' => 'Other',
            default => ucfirst($this->usage_type),
        };
    }
}
