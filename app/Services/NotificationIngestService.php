<?php

namespace App\Services;

use App\Models\LedgerEntry;
use App\Models\PaymentNotification;
use Illuminate\Support\Facades\DB;

class NotificationIngestService
{
    public function __construct(
        private NotificationParseService $parser,
        private NotificationMatchService $matcher
    ) {}

    public function handle(array $payload): array
    {
        return DB::transaction(function () use ($payload) {
            $deviceId   = (string) ($payload['device_id'] ?? '');
            $postedAt   = (string) ($payload['posted_at'] ?? '');
            $rawText    = (string) ($payload['raw_text'] ?? '');
            $title      = (string) ($payload['title'] ?? '');

            // Hash for idempotency
            $hash = hash('sha256', $deviceId . '|' . $postedAt . '|' . $rawText);

            // If already exists, return it (do not apply twice)
            $existing = PaymentNotification::where('hash', $hash)->first();
            if ($existing) {
                return [
                    'notification' => $existing,
                    'already_processed' => true,
                ];
            }

            // Parse
            [$amount, $sender, $txnId, $parseConfidence] = $this->parser->parse($title, $rawText);

            // Store notification
            $notif = PaymentNotification::create([
                'device_id'        => $deviceId,
                'android_package'  => $payload['android_package'] ?? null,
                'notification_uid' => $payload['notification_uid'] ?? null,
                'title'            => $title ?: null,
                'raw_text'         => $rawText,
                'posted_at'        => $postedAt,
                'hash'             => $hash,

                'parsed_amount'    => $amount,
                'parsed_sender'    => $sender,
                'parsed_txn_id'    => $txnId,
                'parse_confidence' => $parseConfidence,

                'status'           => 'unmatched',
            ]);

            // Match
            [$colleagueId, $matchConfidence, $strategy] = $this->matcher->match($sender);

            if ($colleagueId) {
                $notif->update([
                    'matched_colleague_id' => $colleagueId,
                    'match_confidence' => $matchConfidence,
                    'match_strategy' => $strategy,
                    'status' => 'matched',
                ]);
            }

            // Auto-apply if match confidence is high and amount exists
            $autoApplied = false;
            if ($colleagueId && $amount !== null && $matchConfidence >= 85) {
                // Extra safety: unique(payment_notification_id) prevents double credit
                LedgerEntry::create([
                    'colleague_id'           => $colleagueId,
                    'entry_type'             => 'credit',
                    'amount'                 => $amount,
                    'source'                 => 'esewa_notification',
                    'payment_notification_id'=> $notif->id,
                    'reference_key'          => $hash,
                    'meta'                   => [
                        'sender' => $sender,
                        'txn_id' => $txnId,
                        'match_confidence' => $matchConfidence,
                        'match_strategy' => $strategy,
                    ],
                ]);

                $notif->update(['status' => 'applied']);
                $autoApplied = true;
            }

            return [
                'notification' => $notif->fresh(),
                'already_processed' => false,
                'auto_applied' => $autoApplied,
            ];
        });
    }
}
