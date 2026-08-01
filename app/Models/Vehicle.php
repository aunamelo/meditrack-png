<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Vehicle extends Model
{
    /**
     * A GPS ping is considered stale (driver's phone likely lost signal or
     * stopped tracking) once it's older than this many minutes.
     */
    public const STALE_AFTER_MINUTES = 10;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'registration',
        'type',
        'depot',
        'is_active',
        'notes',
        'last_latitude',
        'last_longitude',
        'last_speed_kmh',
        'last_ping_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'last_latitude' => 'decimal:7',
        'last_longitude' => 'decimal:7',
        'last_speed_kmh' => 'decimal:2',
        'last_ping_at' => 'datetime',
    ];

    public function stockTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(VehicleLocation::class);
    }

    public function latestLocation(): HasOne
    {
        return $this->hasOne(VehicleLocation::class)->latestOfMany('recorded_at');
    }

    /**
     * The road delivery (Lae AMS -> Modilon) this vehicle is currently
     * carrying, if any — the leg that GPS tracking applies to.
     */
    public function activeShipment(): ?StockTransfer
    {
        return $this->stockTransfers()
            ->where('status', 'sent')
            ->where('to_level', 'modilon_hospital')
            ->latest('sent_date')
            ->first();
    }

    public function hasKnownLocation(): bool
    {
        return $this->last_ping_at !== null;
    }

    public function isTrackingStale(): bool
    {
        return $this->last_ping_at === null
            || $this->last_ping_at->lt(now()->subMinutes(self::STALE_AFTER_MINUTES));
    }

    /**
     * Record a new GPS ping and update the cached last-known position.
     */
    public function recordPing(array $data): VehicleLocation
    {
        $location = $this->locations()->create($data);

        $this->forceFill([
            'last_latitude' => $data['latitude'],
            'last_longitude' => $data['longitude'],
            'last_speed_kmh' => $data['speed_kmh'] ?? null,
            'last_ping_at' => $data['recorded_at'],
        ])->save();

        return $location;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAtDepot($query, string $depot)
    {
        return $query->where('depot', $depot);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'truck' => 'Truck',
            'van' => 'Van',
            'ute' => 'Ute',
            default => ucfirst($this->type),
        };
    }

    public function displayLabel(): string
    {
        return "{$this->name} ({$this->registration})";
    }
}
