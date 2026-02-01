<?php

namespace App\Services;

use App\Models\LedgerEntry;
use App\Models\OrderBatch;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FinalizeOrderBatchService
{
    public function handle(int $batchId): OrderBatch
    {
        return DB::transaction(function () use ($batchId) {
            /** @var OrderBatch $batch */
            $batch = OrderBatch::query()->lockForUpdate()->findOrFail($batchId);

            if ($batch->status !== 'draft') {
                throw new RuntimeException("Batch must be draft to finalize.");
            }

            // ✅ Guard: if finalize was called earlier (or partially), prevent duplicate debits
            $alreadyDebited = LedgerEntry::query()
                ->where('source', 'order_batch')
                ->where('order_batch_id', $batch->id)
                ->exists();

            if ($alreadyDebited) {
                $batch->update(['status' => 'finalized']);
                return $batch->fresh();
            }

            // Group totals by colleague
            $totals = $batch->items()
                ->selectRaw('colleague_id, SUM(line_total) as total')
                ->groupBy('colleague_id')
                ->get();

            if ($totals->isEmpty()) {
                throw new RuntimeException("Cannot finalize an empty batch.");
            }

            foreach ($totals as $row) {
                $amount = (float) $row->total;
                if ($amount <= 0) continue;

                LedgerEntry::create([
                    'colleague_id'   => $row->colleague_id,
                    'entry_type'     => 'debit',
                    'amount'         => $amount,
                    'source'         => 'order_batch',
                    'order_batch_id' => $batch->id,
                    'meta'           => [
                        'reason' => 'food_batch_finalized',
                        'batch_title' => $batch->title,
                        'vendor_name' => $batch->vendor_name,
                    ],
                ]);
            }

            $batch->update(['status' => 'finalized']);

            return $batch->fresh();
        });
    }
}
