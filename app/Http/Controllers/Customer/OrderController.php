<?php

namespace App\Http\Controllers\Customer;

use App\Events\OrderCancelled;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CancelOrderRequest;
use App\Http\Requests\Customer\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderMatchingService;
use App\Support\OrderLifecycle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class OrderController extends Controller
{
    public function __construct(private readonly OrderMatchingService $orderMatchingService)
    {
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = DB::transaction(function () use ($request): Order {
            $data = $request->validated();

            return Order::query()->create([
                'customer_id' => $request->user()->id,
                'product_id' => $data['product_id'],
                'measurements' => $data['measurements'],
                'delivery_latitude' => $data['customer_location']['latitude'],
                'delivery_longitude' => $data['customer_location']['longitude'],
                'delivery_location' => DB::raw(sprintf(
                    'ST_SetSRID(ST_MakePoint(%F, %F), 4326)',
                    (float) $data['customer_location']['longitude'],
                    (float) $data['customer_location']['latitude'],
                )),
                'status' => Order::STATUS_SEARCHING_FOR_TAILOR,
            ]);
        });

        $order->load(['customer', 'product.category']);
        $tailors = $this->orderMatchingService->findNearbyTailors($order, 20);

        if ($tailors->isEmpty()) {
            $order->update(['status' => Order::STATUS_NO_TAILORS_AVAILABLE]);

            return response()->json([
                'message' => 'No available tailors were found.',
                'data' => new OrderResource($order->fresh(['customer', 'product.category'])),
                'meta' => [
                    'matching_status' => Order::STATUS_NO_TAILORS_AVAILABLE,
                    'matched_tailors_count' => 0,
                ],
            ], 201);
        }

        $this->orderMatchingService->broadcastOrderToTailors($order, $tailors);

        return response()->json([
            'message' => 'Order created and broadcast to nearby tailors.',
            'data' => new OrderResource($order),
            'meta' => [
                'matching_status' => Order::STATUS_SEARCHING_FOR_TAILOR,
                'matched_tailors_count' => $tailors->count(),
            ],
        ], 201);
    }

    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order->load(['customer', 'product.category', 'tailor.tailorProfile.category', 'review']);

        return response()->json([
            'data' => new OrderResource($order),
        ]);
    }

    public function active(Request $request)
    {
        $orders = Order::query()
            ->where('customer_id', $request->user()->id)
            ->whereIn('status', OrderLifecycle::customerActiveStatuses())
            ->with(['tailor.tailorProfile.category', 'product.category'])
            ->latest('updated_at')
            ->paginate(20);

        return OrderResource::collection($orders)->additional([
            'meta' => ['scope' => 'active'],
        ]);
    }

    public function history(Request $request)
    {
        $orders = Order::query()
            ->where('customer_id', $request->user()->id)
            ->whereIn('status', OrderLifecycle::customerHistoryStatuses())
            ->with(['tailor.tailorProfile.category', 'product.category', 'review'])
            ->latest('updated_at')
            ->paginate(20);

        return OrderResource::collection($orders)->additional([
            'meta' => ['scope' => 'history'],
        ]);
    }

    public function cancel(CancelOrderRequest $request, Order $order): JsonResponse
    {
        $this->authorize('cancelByCustomer', $order);

        if (! in_array($order->status, OrderLifecycle::customerCancellableStatuses(), true)) {
            return response()->json([
                'message' => 'Order cannot be cancelled from its current status.',
                'meta' => [
                    'current_status' => $order->status,
                    'allowed_cancel_statuses' => OrderLifecycle::customerCancellableStatuses(),
                ],
            ], 422);
        }

        $warning = $order->status === Order::STATUS_PROCESSING
            ? 'Cancelling while processing may trigger policy penalties.'
            : null;

        $order->update([
            'status' => Order::STATUS_CANCELLED_BY_CUSTOMER,
            'cancellation_reason' => $request->validated('reason'),
        ]);

        $order->refresh()->load(['customer', 'tailor', 'product.category', 'review']);

        if ($order->tailor_id !== null) {
            Event::dispatch(new OrderCancelled($order));
        }

        return response()->json([
            'message' => 'Order cancelled successfully.',
            'data' => new OrderResource($order),
            'meta' => [
                'warning' => $warning,
            ],
        ]);
    }
}