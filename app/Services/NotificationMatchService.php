<?php

namespace App\Services;

use App\Models\Colleague;
use App\Models\ColleagueAlias;

class NotificationMatchService
{
    public function match(?string $sender): array
    {
        if (!$sender) {
            return [null, 0, null]; // colleague_id, confidence, strategy
        }

        $normalized = ColleagueAlias::normalize($sender);

        // 1) Exact name match (normalized)
        $candidate = Colleague::query()
            ->get()
            ->first(function ($c) use ($normalized) {
                return ColleagueAlias::normalize($c->display_name) === $normalized;
            });

        if ($candidate) {
            return [$candidate->id, 95, 'exact'];
        }

        // 2) Alias match
        $alias = ColleagueAlias::query()
            ->where('normalized_alias', $normalized)
            ->first();

        if ($alias) {
            return [$alias->colleague_id, 90, 'alias'];
        }

        // 3) Simple fuzzy: contains match (low confidence)
        $all = Colleague::all();
        $hits = $all->filter(function ($c) use ($normalized) {
            $name = ColleagueAlias::normalize($c->display_name);
            return $name && (str_contains($name, $normalized) || str_contains($normalized, $name));
        })->values();

        if ($hits->count() === 1) {
            return [$hits->first()->id, 70, 'fuzzy'];
        }

        // ambiguous or none
        return [null, 0, null];
    }
}
