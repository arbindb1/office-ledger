<?php

namespace App\Services;

class NotificationParseService
{
    /**
     * Returns: [amount, sender, txn_id, confidence]
     */
    public function parse(string $title = null, string $rawText = ''): array
    {
        $text = $rawText;

        // Amount patterns: Rs 500, NPR 500, रू 500, 500.00 etc.
        $amount = null;
        $confidence = 0;

        if (preg_match('/(?:rs\.?|npr|रु|रू)\s*([0-9]+(?:\.[0-9]{1,2})?)/i', $text, $m)) {
            $amount = (float) $m[1];
            $confidence += 60;
        } elseif (preg_match('/\b([0-9]+(?:\.[0-9]{1,2})?)\b/', $text, $m)) {
            // fallback: first number seen (lower confidence)
            $amount = (float) $m[1];
            $confidence += 25;
        }

        // Sender extraction (best effort, will vary by notification format)
        $sender = null;
        if (preg_match('/from\s+([A-Za-z0-9 ._-]{2,})/i', $text, $m)) {
            $sender = trim($m[1]);
            $confidence += 20;
        } elseif (preg_match('/sender[:\s]+([A-Za-z0-9 ._-]{2,})/i', $text, $m)) {
            $sender = trim($m[1]);
            $confidence += 20;
        }

        // Txn ID extraction
        $txnId = null;
        if (preg_match('/(?:txn|tx|transaction)\s*id[:\s]*([A-Za-z0-9-]+)/i', $text, $m)) {
            $txnId = trim($m[1]);
            $confidence += 10;
        }

        // Clamp confidence
        $confidence = max(0, min(100, $confidence));

        return [$amount, $sender, $txnId, $confidence];
    }
}
