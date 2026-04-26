<?php

namespace App\Http\Controllers\Customer;

use App\Events\OrderCancelled;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CancelOrderRequest;
use App\Http\Requests\Customer\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderMatchingService;
use App\Services\OrderFinancialsService;
use App\Support\OrderLifecycle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderMatchingService $orderMatchingService,
        private readonly OrderFinancialsService $orderFinancialsService,
    )
    {
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $product = Product::query()
            ->with([
                'fabric',
                'measurements' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('name'),
            ])
            ->findOrFail((int) $request->validated('product_id'));

        $order = DB::transaction(function () use ($request, $product): Order {
            $data = $request->validated();
            $normalizedMeasurements = $this->normalizeMeasurements($product, $data['measurements']);
            $financials = $this->orderFinancialsService->snapshotForProduct($product);

            return Order::query()->create([
                'customer_id' => $request->user()->id,
                'product_id' => $data['product_id'],
                'measurements' => $normalizedMeasurements,
                'delivery_latitude' => $data['customer_location']['latitude'],
                'delivery_longitude' => $data['customer_location']['longitude'],
                'delivery_work_wilaya' => $data['customer_location']['work_wilaya'] ?? null,
                'delivery_location_label' => $data['customer_location']['label'] ?? null,
                'delivery_location' => DB::raw(sprintf(
                    'ST_SetSRID(ST_MakePoint(%F, %F), 4326)',
                    (float) $data['customer_location']['longitude'],
                    (float) $data['customer_location']['latitude'],
                )),
                'subtotal_amount' => $financials['subtotal_amount'],
                'shipping_amount' => $financials['shipping_amount'],
                'platform_commission_amount' => $financials['platform_commission_amount'],
                'tailor_net_amount' => $financials['tailor_net_amount'],
                'status' => Order::STATUS_SEARCHING_FOR_TAILOR,
            ]);
        });

        $order->load(['customer', 'product.category', 'product.fabric']);
        $tailors = $this->orderMatchingService->findNearbyTailors($order, 20);
        $matchingSnapshot = $this->orderMatchingService->buildMatchingSnapshot($order, $tailors);

        $order->forceFill([
            'matched_specialization' => $matchingSnapshot['resolved_specialization'] ?? null,
            'matching_snapshot' => $matchingSnapshot,
        ])->save();

        if ($tailors->isEmpty()) {
            $order->update(['status' => Order::STATUS_NO_TAILORS_AVAILABLE]);

            return response()->json([
                'message' => __('messages.orders.no_tailors_found'),
                'data' => new OrderResource($order->fresh(['customer', 'product.category', 'product.fabric'])),
                'meta' => [
                    'matching_status' => Order::STATUS_NO_TAILORS_AVAILABLE,
                    'matched_tailors_count' => 0,
                    'matched_specialization' => $matchingSnapshot['resolved_specialization'] ?? null,
                ],
            ], 201);
        }

        $this->orderMatchingService->broadcastOrderToTailors($order, $tailors);

        return response()->json([
            'message' => __('messages.orders.created_and_broadcast'),
            'data' => new OrderResource($order),
            'meta' => [
                'matching_status' => Order::STATUS_SEARCHING_FOR_TAILOR,
                'matched_tailors_count' => $tailors->count(),
                'matched_specialization' => $matchingSnapshot['resolved_specialization'] ?? null,
                'recommended_tailor_id' => $matchingSnapshot['recommended_tailor_id'] ?? null,
            ],
        ], 201);
    }

    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order->load(['customer', 'product.category', 'product.fabric', 'tailor.tailorProfile.category', 'review']);

        return response()->json([
            'data' => new OrderResource($order),
        ]);
    }

    public function active(Request $request)
    {
        $orders = Order::query()
            ->where('customer_id', $request->user()->id)
            ->whereIn('status', OrderLifecycle::customerActiveStatuses())
            ->with(['tailor.tailorProfile.category', 'product.category', 'product.fabric'])
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
            ->with(['tailor.tailorProfile.category', 'product.category', 'product.fabric', 'review'])
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
                'message' => __('messages.orders.cannot_cancel_status'),
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

        $order->refresh()->load(['customer', 'tailor', 'product.category', 'product.fabric', 'review']);

        if ($order->tailor_id !== null) {
            Event::dispatch(new OrderCancelled($order));
        }

        return response()->json([
            'message' => __('messages.orders.cancelled_success'),
            'data' => new OrderResource($order),
            'meta' => [
                'warning' => $warning,
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $rawMeasurements
     * @return array<string, float>
     */
    private function normalizeMeasurements(Product $product, array $rawMeasurements): array
    {
        $definitions = $product->measurements;

        if ($definitions->isEmpty()) {
            return collect($rawMeasurements)
                ->filter(fn ($value): bool => is_numeric($value) && (float) $value > 0)
                ->mapWithKeys(fn ($value, $key): array => [(string) $key => round((float) $value, 2)])
                ->all();
        }

        return $definitions
            ->mapWithKeys(function ($definition) use ($rawMeasurements): array {
                $slug = (string) $definition->slug;
                $value = $rawMeasurements[$slug] ?? null;

                return [$slug => round((float) $value, 2)];
            })
            ->all();
    }
}
