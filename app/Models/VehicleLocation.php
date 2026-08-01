<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleLocation extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'vehicle_id',
        'stock_transfer_id',
        'latitude',
        'longitude',
        'speed_kmh',
        'heading',
        'accuracy_meters',
        'recorded_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'speed_kmh' => 'decimal:2',
        'heading' => 'decimal:2',
        'accuracy_meters' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function stockTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class);
    }
}
