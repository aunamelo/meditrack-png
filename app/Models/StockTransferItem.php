<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferItem extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'stock_transfer_id',
        'hospital_order_item_id',
        'drug_id',
        'destination_drug_id',
        'batch_number',
        'quantity_sent',
        'quantity_received',
    ];

    public function stockTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class);
    }

    public function hospitalOrderItem(): BelongsTo
    {
        return $this->belongsTo(HospitalOrderItem::class);
    }

    public function drug(): BelongsTo
    {
        return $this->belongsTo(Drug::class);
    }

    public function destinationDrug(): BelongsTo
    {
        return $this->belongsTo(Drug::class, 'destination_drug_id');
    }
}
