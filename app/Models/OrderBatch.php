<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderBatch extends Model
{
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

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_batch_id');
    }
}
