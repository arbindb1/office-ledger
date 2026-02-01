<?php

namespace App\Http\Controllers;

use App\Models\OrderBatch;
use App\Models\OrderItem;
use App\Services\FinalizeOrderBatchService;
use Illuminate\Http\Request;

class OrderBatchController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'vendor_name' => ['nullable', 'string', 'max:255'],
            'ordered_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $batch = OrderBatch::create([
            ...$data,
            'status' => 'draft',
        ]);

        return response()->json(['success' => true, 'data' => $batch]);
    }

    public function finalize(FinalizeOrderBatchService $service, int $batchId)
    {
        $batch = $service->handle($batchId);

        return response()->json([
            'success' => true,
            'message' => 'Batch finalized and debits created.',
            'data' => $batch,
        ]);
    }

    public function addItem(Request $request, int $batchId)
    {
        $batch = OrderBatch::findOrFail($batchId);

        if ($batch->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot add items to a non-draft batch.',
            ], 422);
        }

        $data = $request->validate([
            'colleague_id' => ['required', 'exists:colleagues,id'],

            // Either item_id or item_name
            'item_id' => ['nullable', 'integer', 'exists:items,id', 'required_without:item_name'],
            'item_name' => ['nullable', 'string', 'max:255', 'required_without:item_id'],

            'quantity' => ['nullable', 'integer', 'min:1'],

            // Optional override (if item_id is used, this overrides default_price)
            'unit_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $qty = (int) ($data['quantity'] ?? 1);

        $itemId = $data['item_id'] ?? null;
        $nameSnapshot = null;
        $unit = null;

        if ($itemId) {
            $catalogItem = \App\Models\Item::findOrFail($itemId);

            if (!$catalogItem->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'This item is inactive.',
                ], 422);
            }

            $nameSnapshot = $catalogItem->name;
            $unit = isset($data['unit_price'])
                ? round((float)$data['unit_price'], 2)
                : (float)$catalogItem->default_price;
        } else {
            $nameSnapshot = $data['item_name'];
            $unit = isset($data['unit_price'])
                ? round((float)$data['unit_price'], 2)
                : null;

            if ($unit === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'unit_price is required when using item_name.',
                ], 422);
            }
        }

        $lineTotal = round($qty * $unit, 2);

        $item = OrderItem::create([
            'order_batch_id' => $batch->id,
            'colleague_id'   => $data['colleague_id'],
            'item_id'        => $itemId,
            'item_name'      => $nameSnapshot,
            'quantity'       => $qty,
            'unit_price'     => $unit,
            'line_total'     => $lineTotal,
        ]);

        return response()->json(['success' => true, 'data' => $item]);
    }

    public function show(int $batchId)
{
    $batch = OrderBatch::with(['items.colleague'])
        ->findOrFail($batchId);

    $total = (float) $batch->items()->sum('line_total');

    $perColleague = $batch->items()
        ->selectRaw('colleague_id, SUM(line_total) as total')
        ->groupBy('colleague_id')
        ->get();

    return response()->json([
        'success' => true,
        'data' => [
            'batch' => $batch,
            'total' => round($total, 2),
            'totals_by_colleague' => $perColleague,
        ],
    ]);
}


}
