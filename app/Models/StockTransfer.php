<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransfer extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'transfer_number',
        'drug_id',
        'destination_drug_id',
        'batch_number',
        'quantity_sent',
        'from_level',
        'to_level',
        'sent_date',
        'status',
        'notes',
        'sent_by',
        'received_by',
        'received_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'sent_date' => 'date',
        'received_at' => 'datetime',
    ];

    /**
     * Generate the next unique transfer number (TRF-YYYY-001).
     */
    public static function generateTransferNumber(): string
    {
        $year = now()->year;
        $prefix = "TRF-{$year}-";

        $last = static::where('transfer_number', 'like', "{$prefix}%")
            ->orderByDesc('transfer_number')
            ->first();

        $nextSequence = $last ? ((int) substr($last->transfer_number, -3)) + 1 : 1;

        return $prefix.str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT);
    }

    public function drug(): BelongsTo
    {
        return $this->belongsTo(Drug::class);
    }

    public function destinationDrug(): BelongsTo
    {
        return $this->belongsTo(Drug::class, 'destination_drug_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function scopeFromLevel($query, string $level)
    {
        return $query->where('from_level', $level);
    }

    public function scopeToLevel($query, string $level)
    {
        return $query->where('to_level', $level);
    }

    public function scopeSentBy($query, int $userId)
    {
        return $query->where('sent_by', $userId);
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function formatSentDate(): string
    {
        return $this->sent_date->format('M d, Y');
    }

    public function getStatusBadge(): string
    {
        return match ($this->status) {
            'sent' => 'blue',
            'received' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    public function canReceive(): bool
    {
        return $this->status === 'sent';
    }

    public function isReceived(): bool
    {
        return $this->status === 'received';
    }

    /**
     * Confirm receipt at Lae AMS.
     */
    public function receive(int $userId, ?string $notes = null): void
    {
        $update = [
            'status' => 'received',
            'received_by' => $userId,
            'received_at' => now(),
        ];

        if ($notes) {
            $update['notes'] = trim(($this->notes ? $this->notes."\n\n" : '').'Receipt note: '.$notes);
        }

        $this->update($update);
    }
}
