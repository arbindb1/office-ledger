<?php

namespace App\Http\Controllers;

use App\Models\Colleague;
use App\Models\ColleagueAlias;
use Illuminate\Http\Request;

class ColleagueController extends Controller
{
    public function index()
    {
        $colleagues = Colleague::query()
            ->select('colleagues.*')
            ->selectSub(function ($q) {
                $q->from('ledger_entries')
                    ->selectRaw("COALESCE(SUM(CASE WHEN entry_type='debit' THEN amount ELSE 0 END),0)
                               - COALESCE(SUM(CASE WHEN entry_type='credit' THEN amount ELSE 0 END),0)")
                    ->whereColumn('ledger_entries.colleague_id', 'colleagues.id');
            }, 'outstanding')
            ->orderByDesc('outstanding')
            ->get();

        return response()->json(['success' => true, 'data' => $colleagues]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        $c = Colleague::create($data);

        return response()->json(['success' => true, 'data' => $c]);
    }

    public function addAlias(Request $request, int $colleagueId)
    {
        $colleague = Colleague::findOrFail($colleagueId);

        $data = $request->validate([
            'alias' => ['required', 'string', 'max:255'],
        ]);

        $norm = ColleagueAlias::normalize($data['alias']);

        $alias = $colleague->aliases()->create([
            'alias' => $data['alias'],
            'normalized_alias' => $norm,
        ]);

        return response()->json(['success' => true, 'data' => $alias]);
    }

    public function ledger(int $colleagueId)
    {
        $colleague = Colleague::findOrFail($colleagueId);

        $ledger = $colleague->ledgerEntries()
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'colleague' => $colleague,
                'outstanding' => $colleague->outstandingBalance(),
                'ledger' => $ledger,
            ],
        ]);
    }
}
