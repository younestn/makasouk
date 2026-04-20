<?php

namespace App\Http\Controllers\Tailor;

use App\Events\OrderAccepted;
use App\Events\OrderCancelledByTailor;
use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tailor\AcceptOrderRequest;
use App\Http\Requests\Tailor\CancelOrderRequest;
use App\Http\Requests\Tailor\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class OrderController extends Controller
{
    public function acceptOrder(AcceptOrderRequest $request, Order $order): JsonResponse
    {
        $this->authorize('acceptByTailor', $order);
        $tailorId = (int) $request->user()->id;

        $result = DB::transaction(function () use ($order, $tailorId) {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->tailor_id !== null) {
                return ['success' => false, 'status' => 409, 'message' => 'عذراً، تم أخذ الطلب', 'order' => $lockedOrder];
            }

            $lockedOrder->update([
                'tailor_id' => $tailorId,
                'status' => Order::STATUS_ACCEPTED,
                'accepted_at' => now(),
            ]);

            return ['success' => true, 'status' => 200, 'message' => 'تم قبول الطلب بنجاح', 'order' => $lockedOrder->fresh(['customer', 'tailor', 'product'])];
        });

        if (! $result['success']) {
            return response()->json(['message' => $result['message'], 'order' => new OrderResource($result['order'])], $result['status']);
        }

        $notifiedTailorIds = collect($request->validated('notified_tailor_ids', []))
            ->map(static fn ($id) => (int) $id)
            ->unique()
            ->whenEmpty(fn ($collection) => $collection->push($tailorId))
            ->values()
            ->all();

        Event::dispatch(new OrderAccepted($result['order'], $tailorId, $notifiedTailorIds));

        return response()->json(['message' => $result['message'], 'order' => new OrderResource($result['order'])]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $this->authorize('updateByTailor', $order);

        $allowedTransitions = [
            Order::STATUS_ACCEPTED => [Order::STATUS_PROCESSING],
            Order::STATUS_PROCESSING => [Order::STATUS_READY_FOR_DELIVERY],
            Order::STATUS_READY_FOR_DELIVERY => [Order::STATUS_COMPLETED],
        ];

        $nextStatus = $request->validated('status');
        $currentStatus = (string) $order->status;

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

        return response()->json(['message' => 'تم تحديث حالة الطلب بنجاح', 'order' => new OrderResource($order)]);
    }

    public function cancel(CancelOrderRequest $request, Order $order): JsonResponse
    {
        $this->authorize('cancelByTailor', $order);

        if (! in_array($order->status, [Order::STATUS_ACCEPTED, Order::STATUS_PROCESSING, Order::STATUS_READY_FOR_DELIVERY], true)) {
            return response()->json(['message' => 'لا يمكن إلغاء هذا الطلب في حالته الحالية.'], 422);
        }

        $order->update(['status' => Order::STATUS_CANCELLED_BY_TAILOR, 'cancellation_reason' => $request->validated('reason')]);
        $order->refresh();

        Event::dispatch(new OrderCancelledByTailor($order));

        return response()->json(['message' => 'تم إلغاء الطلب من طرف الخياط.', 'order' => new OrderResource($order)]);
    }

    public function history(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->where('tailor_id', $request->user()->id)
            ->where('status', Order::STATUS_COMPLETED)
            ->with(['customer', 'product.category', 'review'])
            ->latest('updated_at')
            ->paginate(20);

        return response()->json(OrderResource::collection($orders));
    }
}
