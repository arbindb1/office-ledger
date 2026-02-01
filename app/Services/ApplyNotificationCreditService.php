<?php

namespace App\Services;

use App\Models\LedgerEntry;
use App\Models\PaymentNotification;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ApplyNotificationCreditService
{
    public function handle(int $notificationId, ?float $overrideAmount = null): PaymentNotification
    {
        return DB::transaction(function () use ($notificationId, $overrideAmount) {
            /** @var PaymentNotification $notif */
            $notif = PaymentNotification::query()->lockForUpdate()->findOrFail($notificationId);

            if ($notif->status === 'ignored') {
                throw new RuntimeException("Cannot apply an ignored notification.");
            }

            if ($notif->status === 'applied') {
                // Already applied; do nothing
                return $notif;
            }

            $colleagueId = $notif->matched_colleague_id;
            if (!$colleagueId) {
                throw new RuntimeException("No colleague assigned. Assign colleague before applying.");
            }

            $amount = $overrideAmount ?? ($notif->parsed_amount !== null ? (float)$notif->parsed_amount : null);
            if ($amount === null || $amount <= 0) {
                throw new RuntimeException("No valid amount found. Provide override_amount to apply.");
            }

            // Extra guard: if any ledger entry already exists for this notification, prevent duplicates
            $already = LedgerEntry::query()
                ->where('payment_notification_id', $notif->id)
                ->exists();

            if ($already) {
                $notif->update(['status' => 'applied']);
                return $notif->fresh();
            }

            LedgerEntry::create([
                'colleague_id'            => $colleagueId,
                'entry_type'              => 'credit',
                'amount'                  => $amount,
                'source'                  => 'esewa_notification',
                'payment_notification_id' => $notif->id,
                'reference_key'           => $notif->hash,
                'meta'                    => [
                    'sender' => $notif->parsed_sender,
                    'txn_id' => $notif->parsed_txn_id,
                    'match_confidence' => $notif->match_confidence,
                    'match_strategy' => $notif->match_strategy,
                    'applied_mode' => $overrideAmount !== null ? 'manual_override_amount' : 'manual_apply',
                ],
            ]);

            $notif->update([
                'status' => 'applied',
                // if we applied with override, store it so UI stays consistent
                'parsed_amount' => $amount,
            ]);

            return $notif->fresh();
        });
    }
}
