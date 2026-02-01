<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Colleague extends Model
{
    protected $fillable = [
        'display_name',
        'phone',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'bool',
    ];

    public function aliases(): HasMany
    {
        return $this->hasMany(ColleagueAlias::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }

    /**
     * Outstanding balance = sum(debits) - sum(credits)
     * Positive means they owe you. Negative means advance/overpay.
     */
    public function outstandingBalance(): float
    {
        $debits = (float) $this->ledgerEntries()
            ->where('entry_type', 'debit')
            ->sum('amount');

        $credits = (float) $this->ledgerEntries()
            ->where('entry_type', 'credit')
            ->sum('amount');

        return round($debits - $credits, 2);
    }
}
