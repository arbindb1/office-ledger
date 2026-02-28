<?php

namespace App\Http\Controllers;

use App\Models\Colleague;
use App\Models\ColleagueAlias;
use App\Models\OrderBatch;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ColleagueController extends Controller
{
public function index(Request $request)
{
    $includeInactive = $request->boolean('include_inactive');

$colleagues = Colleague::query()
    ->select('colleagues.*')
    ->selectSub(function ($q) {
        $q->from('ledger_entries')
            ->selectRaw("
                COALESCE(SUM(CASE WHEN entry_type='debit' THEN amount ELSE 0 END), 0)
              - COALESCE(SUM(CASE WHEN entry_type='credit' THEN amount ELSE 0 END), 0)
            ")
            ->whereColumn('ledger_entries.colleague_id', 'colleagues.id')
            // Add the soft delete check here
            ->whereNull('deleted_at'); 
    }, 'outstanding')
    ->when(!$includeInactive, function ($q) {
        $q->where('is_active', true);
    })
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

        $batchIds = $ledger->pluck('order_batch_id')->filter()->unique();

        $batchMap = OrderBatch::whereIn('id', $batchIds)
            ->pluck('title', 'id');

        $ledger->each(function ($entry) use ($batchMap) {
            $entry->batch_name = $batchMap->get($entry->order_batch_id);
        });

        Log::info("ledger");
        Log::info($ledger);
        return response()->json([
            'success' => true,
            'data' => [
                'colleague' => $colleague,
                'outstanding' => $colleague->outstandingBalance(),
                'ledger' => $ledger,
            ],
        ]);
    }

    public function deactivate(Colleague $colleague)
{

    $colleague->update([
        'is_active' => false,
    ]);


    return response()->json([
        'success' => true,
        'message' => 'Colleague deactivated',
        'data' => $colleague,
    ]);
}

public function analytics(Request $request, $id)
{
    $month = $request->query('month');
    $year = $request->query('year');
    $day   = $request->query('day'); 

    $query= DB::table('order_items')
        ->where('colleague_id', $id)
        ->whereNull('deleted_at')
        ->whereMonth('created_at', $month)
        ->whereYear('created_at', $year);


    if ($day) {
        $query->whereDay('created_at', $day);
    }
    $stats = $query->groupBy('item_name')
        ->selectRaw('item_name as item_name, SUM(unit_price) as total_spent')
        ->get();
    $formattedData = $stats->pluck('total_spent', 'item_name');
    Log::info("formatted data");
    Log::info($formattedData);

    return response()->json($formattedData->isEmpty() ? (object)[] : $formattedData);
}

}
