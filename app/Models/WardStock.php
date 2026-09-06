<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WardStock extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'ward_id',
        'drug_id',
        'quantity_on_hand',
        'reorder_point',
        'last_issued_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'quantity_on_hand' => 'integer',
        'reorder_point' => 'integer',
        'last_issued_at' => 'datetime',
    ];

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function drug(): BelongsTo
    {
        return $this->belongsTo(Drug::class);
    }

    public function isLowStock(): bool
    {
        return $this->quantity_on_hand <= $this->reorder_point;
    }
}
