<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ColleagueAlias extends Model
{
    protected $fillable = [
        'colleague_id',
        'alias',
        'normalized_alias',
    ];

    public function colleague(): BelongsTo
    {
        return $this->belongsTo(Colleague::class);
    }

    public static function normalize(string $s): string
    {
        $s = mb_strtolower(trim($s));
        $s = preg_replace('/\s+/', ' ', $s);
        $s = preg_replace('/[^\p{L}\p{N}\s]/u', '', $s);
        return $s ?? '';
    }
}
