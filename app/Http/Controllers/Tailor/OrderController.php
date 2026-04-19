<?php

namespace App\Http\Controllers\Tailor;

use App\Events\OrderAccepted;
use App\Events\OrderCancelledByTailor;
use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class OrderController extends Controller
{
    public function acceptOrder(Request $request, Order $order): JsonResponse
    {
        abort_unless($request->user()?->role === 'tailor', 403);

        $tailorId = (int) $request->user()->id;

        $validated = $request->validate([
            'notified_tailor_ids' => ['nullable', 'array'],
            'notified_tailor_ids.*' => ['integer', 'distinct'],
        ]);

        $result = DB::transaction(function () use ($order, $tailorId) {
            $lockedOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->tailor_id !== null) {
                return [
                    'success' => false,
                    'status' => 409,
                    'message' => 'عذراً، تم أخذ الطلب',
                    'order' => $lockedOrder,
                ];
            }

            $lockedOrder->update([
                'tailor_id' => $tailorId,
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);

            return [
                'success' => true,
                'status' => 200,
                'message' => 'تم قبول الطلب بنجاح',
                'order' => $lockedOrder->fresh(['customer', 'tailor', 'product']),
            ];
        });

        if (! $result['success']) {
            return response()->json([
                'message' => $result['message'],
                'order' => $result['order'],
            ], $result['status']);
        }

        $notifiedTailorIds = collect($validated['notified_tailor_ids'] ?? [])
            ->map(static fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($notifiedTailorIds === []) {
            $notifiedTailorIds = [$tailorId];
        }

        Event::dispatch(new OrderAccepted($result['order'], $tailorId, $notifiedTailorIds));

        return response()->json([
            'message' => $result['message'],
            'order' => $result['order'],
        ], 200);
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        abort_unless($request->user()?->role === 'tailor', 403);
        abort_unless((int) $order->tailor_id === (int) $request->user()->id, 403);

        $validated = $request->validate([
            'status' => ['required', 'in:processing,ready_for_delivery,completed'],
        ]);

        $allowedTransitions = [
            'accepted' => ['processing'],
            'processing' => ['ready_for_delivery'],
            'ready_for_delivery' => ['completed'],
        ];

        $currentStatus = (string) $order->status;
        $nextStatus = $validated['status'];

        if (! isset($allowedTransitions[$currentStatus]) || ! in_array($nextStatus, $allowedTransitions[$currentStatus], true)) {
            return response()->json([
                'message' => 'الانتقال بين الحالات غير مسموح',
                'current_status' => $currentStatus,
                'allowed_next_statuses' => $allowedTransitions[$currentStatus] ?? [],
            ], 422);
        }

        $order->update(['status' => $nextStatus]);
        $order->refresh();

        Event::dispatch(new OrderStatusUpdated($order));

        return response()->json([
            'message' => 'تم تحديث حالة الطلب بنجاح',
            'order' => $order,
        ]);
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        abort_unless($request->user()?->role === 'tailor', 403);
        abort_unless((int) $order->tailor_id === (int) $request->user()->id, 403);

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        if (! in_array($order->status, ['accepted', 'processing', 'ready_for_delivery'], true)) {
            return response()->json([
                'message' => 'لا يمكن إلغاء هذا الطلب في حالته الحالية.',
            ], 422);
        }

        $order->update([
            'status' => 'cancelled_by_tailor',
            'cancellation_reason' => $validated['reason'] ?? null,
        ]);

        $order->refresh();
        Event::dispatch(new OrderCancelledByTailor($order));

        return response()->json([
            'message' => 'تم إلغاء الطلب من طرف الخياط.',
            'order' => $order,
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        abort_unless($request->user()?->role === 'tailor', 403);

        $orders = Order::query()
            ->where('tailor_id', $request->user()->id)
            ->where('status', 'completed')
            ->with(['customer', 'product.category', 'review'])
            ->latest('updated_at')
            ->paginate(20);

        return response()->json(['data' => $orders]);
    }
}
