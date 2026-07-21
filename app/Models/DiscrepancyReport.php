<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscrepancyReport extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'report_number',
        'hospital_order_id',
        'stock_transfer_id',
        'issue_type',
        'quantity_expected',
        'quantity_received',
        'description',
        'status',
        'resolution_notes',
        'reported_by',
        'resolved_by',
        'resolved_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public static function generateReportNumber(): string
    {
        $year = now()->year;
        $prefix = "DIS-{$year}-";

        $last = static::where('report_number', 'like', "{$prefix}%")
            ->orderByDesc('report_number')
            ->first();

        $nextSequence = $last ? ((int) substr($last->report_number, -3)) + 1 : 1;

        return $prefix.str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT);
    }

    public function hospitalOrder(): BelongsTo
    {
        return $this->belongsTo(HospitalOrder::class);
    }

    public function stockTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function getIssueTypeLabel(): string
    {
        return match ($this->issue_type) {
            'short_shipment' => 'Short delivery',
            'damaged' => 'Damaged goods',
            'wrong_item' => 'Wrong item',
            'expired' => 'Expired product',
            default => 'Other',
        };
    }
}
