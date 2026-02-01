<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerEntry extends Model
{
    protected $fillable = [
        'colleague_id',
        'entry_type', // debit|credit
        'amount',
        'source', // order_batch|esewa_notification|manual_adjustment
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
