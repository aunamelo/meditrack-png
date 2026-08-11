<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HospitalOrderItem extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'hospital_order_id',
        'drug_name',
        'dosage',
        'quantity_requested',
        'quantity_approved',
        'source_drug_id',
    ];

    public function hospitalOrder(): BelongsTo
    {
        return $this->belongsTo(HospitalOrder::class);
    }

    public function sourceDrug(): BelongsTo
    {
        return $this->belongsTo(Drug::class, 'source_drug_id');
    }

    public function displayLabel(): string
    {
        return trim($this->drug_name.($this->dosage ? ' ('.$this->dosage.')' : ''));
    }
}
