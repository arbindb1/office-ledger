<?php

namespace App\Http\Controllers;

use App\Models\LedgerEntry;
use Illuminate\Http\Request;

class LedgerQueryController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'colleague_id' => ['nullable', 'integer'],
            'source' => ['nullable', 'string'],
            'type' => ['nullable', 'in:debit,credit'],
        ]);

        $q = LedgerEntry::query()->with('colleague');

        if (!empty($data['colleague_id'])) $q->where('colleague_id', $data['colleague_id']);
        if (!empty($data['source'])) $q->where('source', $data['source']);
        if (!empty($data['type'])) $q->where('entry_type', $data['type']);

        $rows = $q->orderByDesc('created_at')
            ->limit(300)
            ->get();

        return response()->json(['success' => true, 'data' => $rows]);
    }
}
