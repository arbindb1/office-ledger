<?php

namespace App\Http\Controllers;

use App\Models\LedgerEntry;
use Illuminate\Http\Request;

class LedgerController extends Controller
{
    public function manualCredit(Request $request)
    {
        $data = $request->validate([
            'colleague_id' => ['required', 'exists:colleagues,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'note' => ['nullable', 'string'],
        ]);

        $entry = LedgerEntry::create([
            'colleague_id' => (int)$data['colleague_id'],
            'entry_type'   => 'credit',
            'amount'       => (float)$data['amount'],
            'source'       => 'manual_adjustment',
            'meta'         => [
                'note' => $data['note'] ?? null,
            ],
        ]);

        return response()->json(['success' => true, 'data' => $entry]);
    }
}
