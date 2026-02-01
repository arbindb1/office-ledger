<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PaymentNotification extends Model
{
    protected $fillable = [
        'device_id',
        'android_package',
        'notification_uid',
        'title',
        'raw_text',
        'posted_at',
        'hash',
        'parsed_amount',
        'parsed_sender',
        'parsed_txn_id',
        'parse_confidence',
        'matched_colleague_id',
        'match_confidence',
        'match_strategy',
        'status',
    ];

    protected $casts = [
        'posted_at' => 'datetime',
        'parsed_amount' => 'decimal:2',
    ];

    public function matchedColleague(): BelongsTo
    {
        return $this->belongsTo(Colleague::class, 'matched_colleague_id');
    }

    public function ledgerEntry(): HasOne
    {
        return $this->hasOne(LedgerEntry::class, 'payment_notification_id');
    }
}
