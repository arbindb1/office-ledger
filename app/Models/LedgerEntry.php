<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LedgerEntry extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'colleague_id',
        'entry_type', 
        'amount',
        'source', 
        'order_batch_id',
        'payment_notification_id',
        'reference_key',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'meta' => 'array',
    ];

    public function colleague(): BelongsTo
    {
        return $this->belongsTo(Colleague::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(OrderBatch::class, 'order_batch_id');
    }

    public function notification(): BelongsTo
    {
        return $this->belongsTo(PaymentNotification::class, 'payment_notification_id');
    }
}
