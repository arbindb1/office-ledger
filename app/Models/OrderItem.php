<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'order_batch_id',
        'colleague_id',
        'item_id',
        'item_name',
        'quantity',
        'unit_price',
        'line_total',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(OrderBatch::class, 'order_batch_id');
    }

    public function colleague(): BelongsTo
    {
        return $this->belongsTo(Colleague::class);
    }
}
