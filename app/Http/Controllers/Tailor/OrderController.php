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
use App\Support\OrderLifecycle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class OrderController extends Controller
{
    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order->load(['customer', 'tailor.tailorProfile.category', 'product.category', 'review']);

        return response()->json([
            'data' => new OrderResource($order),
        ]);
    }

    public function acceptOrder(AcceptOrderRequest $request, Order $order): JsonResponse
    {
        $this->authorize('acceptByTailor', $order);
        $tailorId = (int) $request->user()->id;

        $result = DB::transaction(function () use ($order, $tailorId): array {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->tailor_id !== null) {
                return [
                    'success' => false,
                    'status' => 409,
                    'message' => 'Order is already accepted by another tailor.',
                    'order' => $lockedOrder,
                ];
            }

            $lockedOrder->update([
                'tailor_id' => $tailorId,
                'status' => Order::STATUS_ACCEPTED,
                'accepted_at' => now(),
            ]);

            return [
                'success' => true,
                'status' => 200,
                'message' => 'Order accepted successfully.',
                'order' => $lockedOrder->fresh(['customer', 'tailor', 'product.category']),
            ];
        });

        if (! $result['success']) {
            return response()->json([
                'message' => $result['message'],
                'data' => new OrderResource($result['order']),
            ], $result['status']);
        }

        $notifiedTailorIds = collect($request->validated('notified_tailor_ids', []))
            ->map(static fn ($id) => (int) $id)
            ->unique()
            ->whenEmpty(fn ($collection) => $collection->push($tailorId))
            ->values()
            ->all();

        Event::dispatch(new OrderAccepted($result['order'], $tailorId, $notifiedTailorIds));

        return response()->json([
            'message' => $result['message'],
            'data' => new OrderResource($result['order']),
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $this->authorize('updateByTailor', $order);

        $nextStatus = $request->validated('status');
        $currentStatus = (string) $order->status;

        if (! OrderLifecycle::canTailorTransition($currentStatus, $nextStatus)) {
            return response()->json([
                'message' => 'Status transition is not allowed.',
                'meta' => [
                    'current_status' => $currentStatus,
                    'allowed_next_statuses' => OrderLifecycle::allowedTailorNextStatuses($currentStatus),
                ],
            ], 422);
        }

        $order->update(['status' => $nextStatus]);
        $order->refresh()->load(['customer', 'tailor', 'product.category', 'review']);

        Event::dispatch(new OrderStatusUpdated($order));

        return response()->json([
            'message' => 'Order status updated successfully.',
            'data' => new OrderResource($order),
        ]);
    }

    public function cancel(CancelOrderRequest $request, Order $order): JsonResponse
    {
        $this->authorize('cancelByTailor', $order);

        if (! in_array($order->status, OrderLifecycle::tailorCancellableStatuses(), true)) {
            return response()->json([
                'message' => 'Order cannot be cancelled from its current status.',
                'meta' => [
                    'current_status' => $order->status,
                    'allowed_cancel_statuses' => OrderLifecycle::tailorCancellableStatuses(),
                ],
            ], 422);
        }

        $order->update([
            'status' => Order::STATUS_CANCELLED_BY_TAILOR,
            'cancellation_reason' => $request->validated('reason'),
        ]);

        $order->refresh()->load(['customer', 'tailor', 'product.category', 'review']);

        Event::dispatch(new OrderCancelledByTailor($order));

        return response()->json([
            'message' => 'Order cancelled by tailor.',
            'data' => new OrderResource($order),
        ]);
    }

    public function active(Request $request)
    {
        $orders = Order::query()
            ->where('tailor_id', $request->user()->id)
            ->whereIn('status', OrderLifecycle::tailorActiveStatuses())
            ->with(['customer', 'product.category'])
            ->latest('updated_at')
            ->paginate(20);

        return OrderResource::collection($orders)->additional([
            'meta' => ['scope' => 'active'],
        ]);
    }

    public function history(Request $request)
    {
        $orders = Order::query()
            ->where('tailor_id', $request->user()->id)
            ->whereIn('status', OrderLifecycle::tailorHistoryStatuses())
            ->with(['customer', 'product.category', 'review'])
            ->latest('updated_at')
            ->paginate(20);

        return OrderResource::collection($orders)->additional([
            'meta' => ['scope' => 'history'],
        ]);
    }
}
