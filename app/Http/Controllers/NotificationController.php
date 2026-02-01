<?php

namespace App\Http\Controllers;

use App\Models\PaymentNotification;
use App\Services\ApplyNotificationCreditService;
use App\Services\NotificationIngestService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function ingest(Request $request, NotificationIngestService $service)
    {
        $data = $request->validate([
            'device_id' => ['required', 'string', 'max:255'],
            'android_package' => ['nullable', 'string', 'max:255'],
            'notification_uid' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'raw_text' => ['required', 'string'],
            'posted_at' => ['required', 'date'],
        ]);

        $result = $service->handle($data);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

 public function unmatched(Request $request)
    {
        $status = $request->query('status'); // unmatched|matched|applied|ignored|null

        $q = PaymentNotification::query();

        if ($status) {
            $q->where('status', $status);
        } else {
            $q->whereIn('status', ['unmatched', 'matched']);
        }

        $items = $q->orderByDesc('posted_at')
            ->limit(300)
            ->get();

        return response()->json(['success' => true, 'data' => $items]);
    }


    public function assign(Request $request, int $notificationId)
    {
        $data = $request->validate([
            'colleague_id' => ['required', 'exists:colleagues,id'],
        ]);

        $notif = PaymentNotification::findOrFail($notificationId);

        $notif->update([
            'matched_colleague_id' => $data['colleague_id'],
            'match_strategy' => 'manual',
            'match_confidence' => 100,
            'status' => 'matched',
        ]);

        return response()->json(['success' => true, 'data' => $notif->fresh()]);
    }

    public function ignore(int $notificationId)
    {
        $notif = PaymentNotification::findOrFail($notificationId);
        $notif->update(['status' => 'ignored']);

        return response()->json(['success' => true, 'data' => $notif->fresh()]);
    }

    public function apply(Request $request, ApplyNotificationCreditService $service, int $notificationId)
    {
        $data = $request->validate([
            'override_amount' => ['nullable', 'numeric', 'min:0.01'],
        ]);

        $override = isset($data['override_amount']) ? (float)$data['override_amount'] : null;

        $notif = $service->handle($notificationId, $override);

        return response()->json([
            'success' => true,
            'message' => 'Credit applied successfully.',
            'data' => $notif,
        ]);
    }

}
