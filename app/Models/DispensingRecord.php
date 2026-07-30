<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DispensingRecord extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'record_number',
        'patient_id',
        'drug_id',
        'quantity_dispensed',
        'prescription_ref',
        'notes',
        'dispensed_by',
        'dispensed_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'dispensed_at' => 'datetime',
    ];

    public static function generateRecordNumber(): string
    {
        $year = now()->year;
        $prefix = "DSP-{$year}-";

        $last = static::where('record_number', 'like', "{$prefix}%")
            ->orderByDesc('record_number')
            ->first();

        $nextSequence = $last ? ((int) substr($last->record_number, -4)) + 1 : 1;

        return $prefix.str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function drug(): BelongsTo
    {
        return $this->belongsTo(Drug::class);
    }

    public function dispenser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }
}
