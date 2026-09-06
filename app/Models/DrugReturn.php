<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrugReturn extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'return_number',
        'ward_id',
        'drug_id',
        'quantity_returned',
        'return_date',
        'return_reason',
        'batch_number',
        'notes',
        'returned_by',
        'received_by',
        'received_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'quantity_returned' => 'integer',
        'return_date' => 'date',
        'received_at' => 'datetime',
    ];

    public static function generateReturnNumber(): string
    {
        $year = now()->year;
        $prefix = "RET-{$year}-";

        $last = static::where('return_number', 'like', "{$prefix}%")
            ->orderByDesc('return_number')
            ->first();

        $nextSequence = $last ? ((int) substr($last->return_number, -4)) + 1 : 1;

        return $prefix.str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function drug(): BelongsTo
    {
        return $this->belongsTo(Drug::class);
    }

    public function returner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function isReceived(): bool
    {
        return $this->received_at !== null;
    }

    public function returnReasonLabel(): string
    {
        return match ($this->return_reason) {
            'excess' => 'Excess Stock',
            'expired' => 'Expired',
            'damaged' => 'Damaged',
            'wrong_batch' => 'Wrong Batch',
            'patient_discharge' => 'Patient Discharge',
            'other' => 'Other',
            default => ucfirst($this->return_reason),
        };
    }
}
