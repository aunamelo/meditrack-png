<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrugIssuance extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'issuance_number',
        'ward_id',
        'drug_id',
        'quantity_issued',
        'issuance_date',
        'batch_number',
        'strength',
        'dosage_form',
        'notes',
        'issued_by',
        'received_by',
        'received_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'quantity_issued' => 'integer',
        'issuance_date' => 'date',
        'received_at' => 'datetime',
    ];

    public static function generateIssuanceNumber(): string
    {
        $year = now()->year;
        $prefix = "ISS-{$year}-";

        $last = static::where('issuance_number', 'like', "{$prefix}%")
            ->orderByDesc('issuance_number')
            ->first();

        $nextSequence = $last ? ((int) substr($last->issuance_number, -4)) + 1 : 1;

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

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function isReceived(): bool
    {
        return $this->received_at !== null;
    }
}
