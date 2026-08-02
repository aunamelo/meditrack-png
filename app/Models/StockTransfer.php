<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class StockTransfer extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'transfer_number',
        'drug_id',
        'destination_drug_id',
        'hospital_order_id',
        'vehicle_id',
        'batch_number',
        'quantity_sent',
        'from_level',
        'to_level',
        'sent_date',
        'expected_arrival_at',
        'status',
        'notes',
        'sent_by',
        'approved_by',
        'approved_at',
        'received_by',
        'received_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'sent_date' => 'date',
        'expected_arrival_at' => 'datetime',
        'approved_at' => 'datetime',
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

    public function hospitalOrder(): BelongsTo
    {
        return $this->belongsTo(HospitalOrder::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function locations(): HasMany
    {
        return $this->hasMany(VehicleLocation::class);
    }

    /**
     * Only the Lae AMS -> Modilon Hospital road leg carries a vehicle and
     * is GPS-tracked; the NDoH -> Lae AMS leg travels by air/sea freight.
     */
    public function isRoadLeg(): bool
    {
        return $this->to_level === 'modilon_hospital' && $this->vehicle_id !== null;
    }

    public function isTrackable(): bool
    {
        return $this->isRoadLeg() && $this->status === 'sent';
    }

    /**
     * Signed, no-login link the driver opens on their phone to start
     * sharing GPS location for this specific delivery. Expires with the
     * shipment window so a stale link can't be reused.
     */
    public function driverTrackingUrl(): ?string
    {
        if (! $this->isRoadLeg()) {
            return null;
        }

        // Prefer the current request host so WhatsApp/SMS links match production HTTPS.
        if (request()) {
            URL::forceRootUrl(request()->root());
            URL::forceScheme(request()->getScheme());
        }

        return URL::temporarySignedRoute(
            'driver-track.show',
            now()->addHours(24),
            ['transfer' => $this->id]
        );
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

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function formatSentDate(): string
    {
        return formatDate($this->sent_date);
    }

    public function formatExpectedArrival(): ?string
    {
        return $this->expected_arrival_at ? formatDateTime($this->expected_arrival_at) : null;
    }

    public function isArrivalOverdue(): bool
    {
        return $this->status === 'sent'
            && $this->expected_arrival_at !== null
            && $this->expected_arrival_at->isPast();
    }

    public function getStatusBadge(): string
    {
        return match ($this->status) {
            'pending' => 'amber',
            'sent' => 'blue',
            'received' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    public function canApprove(): bool
    {
        return $this->status === 'pending'
            && $this->from_level === 'ndoh'
            && $this->to_level === 'lae_ams'
            && $this->hospital_order_id === null;
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
     * Progress stages for NDoH → Lae AMS national shipments (not hospital road legs).
     *
     * @return array<int, array{key: string, label: string, date: \Carbon\Carbon|null, completed: bool, current: bool}>
     */
    public function pipelineStages(): array
    {
        $stages = [
            ['key' => 'pending', 'label' => 'Awaiting NDoH approval', 'date' => $this->created_at],
            ['key' => 'sent', 'label' => 'In transit to Lae AMS', 'date' => $this->approved_at ?? ($this->status !== 'pending' ? $this->sent_date : null)],
            ['key' => 'received', 'label' => 'Received at Lae AMS', 'date' => $this->received_at],
        ];

        $currentIndex = match ($this->status) {
            'pending' => 0,
            'sent' => 1,
            'received' => 2,
            'cancelled' => 0,
            default => 0,
        };

        return collect($stages)->values()->map(function (array $stage, int $index) use ($currentIndex) {
            return [
                'key' => $stage['key'],
                'label' => $stage['label'],
                'date' => $stage['date'],
                'completed' => $this->status === 'received' ? true : $index < $currentIndex,
                'current' => $this->status === 'received' ? false : $index === $currentIndex,
            ];
        })->all();
    }

    public function pipelineProgressPercentage(): int
    {
        if ($this->status === 'received') {
            return 100;
        }

        return match ($this->status) {
            'pending' => 0,
            'sent' => 50,
            default => 0,
        };
    }

    /**
     * Admin approval: deduct NDoH stock and mark shipment as sent (in transit).
     * Lae AMS inventory is created only when the Store Manager confirms receipt.
     */
    public function approve(int $userId): void
    {
        if (! $this->canApprove()) {
            throw ValidationException::withMessages([
                'status' => 'This shipment cannot be approved.',
            ]);
        }

        DB::transaction(function () use ($userId) {
            $sourceDrug = Drug::query()->lockForUpdate()->findOrFail($this->drug_id);
            $quantitySent = (int) $this->quantity_sent;

            if ($sourceDrug->quantity_on_hand < $quantitySent) {
                throw ValidationException::withMessages([
                    'quantity_sent' => 'NDoH stock is no longer sufficient for this shipment. Available: '.$sourceDrug->quantity_on_hand.'.',
                ]);
            }

            $sourceDrug->update([
                'quantity_on_hand' => $sourceDrug->quantity_on_hand - $quantitySent,
                'last_issued_date' => now(),
                'updated_by' => $userId,
            ]);

            $this->update([
                'status' => 'sent',
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);
        });
    }

    /**
     * Confirm receipt at Lae AMS and create the warehouse inventory batch.
     */
    public function receive(int $userId, ?string $notes = null): void
    {
        if (! $this->canReceive()) {
            throw ValidationException::withMessages([
                'status' => 'This shipment cannot be received.',
            ]);
        }

        DB::transaction(function () use ($userId, $notes) {
            $sourceDrug = Drug::query()->lockForUpdate()->findOrFail($this->drug_id);
            $quantitySent = (int) $this->quantity_sent;

            $destinationDrugId = $this->destination_drug_id;

            if (! $destinationDrugId) {
                $destinationBatch = $sourceDrug->batch_number.'-LAE-'.now()->format('ymdHis');

                $destinationDrug = Drug::create([
                    'medicine_id' => $sourceDrug->medicine_id,
                    'drug_name' => $sourceDrug->drug_name,
                    'description' => $sourceDrug->description,
                    'dosage' => $sourceDrug->dosage,
                    'dosage_form' => $sourceDrug->dosage_form,
                    'batch_number' => $destinationBatch,
                    'expiry_date' => $sourceDrug->expiry_date,
                    'quantity_received' => $quantitySent,
                    'quantity_on_hand' => $quantitySent,
                    'reorder_point' => $sourceDrug->reorder_point,
                    'unit' => $sourceDrug->unit,
                    'supplier' => $sourceDrug->supplier,
                    'cost_per_unit' => $sourceDrug->cost_per_unit,
                    'storage_location' => 'Lae AMS Warehouse',
                    'level' => 'lae_ams',
                    'status' => 'active',
                    'received_date' => now(),
                    'notes' => "Received via shipment {$this->transfer_number} from NDoH",
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);

                $destinationDrugId = $destinationDrug->id;
            }

            $update = [
                'destination_drug_id' => $destinationDrugId,
                'status' => 'received',
                'received_by' => $userId,
                'received_at' => now(),
            ];

            if ($notes) {
                $update['notes'] = trim(($this->notes ? $this->notes."\n\n" : '').'Receipt note: '.$notes);
            }

            $this->update($update);
        });
    }
}
