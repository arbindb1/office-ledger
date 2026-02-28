<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderBatch extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'title',
        'vendor_name',
        'ordered_at',
        'status',
        'notes',
        'created_by_user_id',
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
    ];

    public function ledgerEntries(): HasMany{
        return $this->hasMany(LedgerEntry::class,'order_batch_id');
    }
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_batch_id');
    }
}
