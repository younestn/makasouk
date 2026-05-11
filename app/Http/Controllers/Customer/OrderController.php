<?php

namespace App\Http\Controllers\Customer;

use App\Events\OrderCancelled;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CancelOrderRequest;
use App\Http\Requests\Customer\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingCompany;
use App\Services\OrderMatchingService;
use App\Services\OrderFinancialsService;
use App\Support\OrderLifecycle;
use App\Support\OrderTracking;
use App\Services\TrackingEventRecorder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderMatchingService $orderMatchingService,
        private readonly OrderFinancialsService $orderFinancialsService,
        private readonly TrackingEventRecorder $trackingEventRecorder,
    )
    {
    }

    public function metadata(Request $request): JsonResponse
    {
        $shippingCompanies = ShippingCompany::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (ShippingCompany $company): array => [
                'id' => $company->id,
                'name' => $company->display_name,
                'description' => $company->display_description,
                'code' => $company->code,
            ])
            ->values()
            ->all();

        return response()->json([
            'data' => [
                'shipping_companies' => $shippingCompanies,
                'delivery_types' => [
                    [
                        'value' => 'office_pickup',
                        'label' => __('messages.orders.delivery_types.office_pickup'),
                    ],
                ],
                'customer' => [
                    'email' => $request->user()?->email,
                ],
            ],
        ]);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $product = Product::query()
            ->with([
                'fabric',
                'measurements' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('name'),
            ])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->findOrFail((int) $request->validated('product_id'));

        $order = DB::transaction(function () use ($request, $product): Order {
            $data = $request->validated();
            $normalizedMeasurements = $this->normalizeMeasurements($product, $data['measurements']);
            $financials = $this->orderFinancialsService->snapshotForProduct($product);
            $shippingCompany = ShippingCompany::query()->findOrFail((int) $data['shipping']['company_id']);
            $orderConfiguration = $this->buildOrderConfiguration($product, $data['configuration'] ?? []);

            return Order::query()->create([
                'customer_id' => $request->user()->id,
                'product_id' => $data['product_id'],
                'measurements' => $normalizedMeasurements,
                'order_configuration' => $orderConfiguration,
                'delivery_latitude' => $data['customer_location']['latitude'],
                'delivery_longitude' => $data['customer_location']['longitude'],
                'delivery_work_wilaya' => $data['customer_location']['work_wilaya'],
                'delivery_commune' => $data['shipping']['commune'],
                'delivery_neighborhood' => $data['shipping']['neighborhood'],
                'delivery_location_label' => $data['customer_location']['label'] ?? null,
                'shipping_company_id' => $shippingCompany->id,
                'shipping_company_name' => $shippingCompany->display_name,
                'delivery_type' => $data['shipping']['delivery_type'],
                'delivery_phone' => $data['shipping']['phone'],
                'delivery_email' => $data['shipping']['email'],
                'subtotal_amount' => $financials['subtotal_amount'],
                'shipping_amount' => $financials['shipping_amount'],
                'platform_commission_amount' => $financials['platform_commission_amount'],
                'tailor_net_amount' => $financials['tailor_net_amount'],
                'status' => Order::STATUS_SEARCHING_FOR_TAILOR,
                'tracking_stage' => OrderTracking::STAGE_TAILOR_ASSIGNMENT_PENDING,
            ]);
        });

        $order->load(['customer', 'product.category', 'product.fabric', 'shippingCompany', 'trackingEvents']);
        $this->trackingEventRecorder->seedInitialOrderTimeline($order);
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
                'data' => new OrderResource($order->fresh(['customer', 'product.category', 'product.fabric', 'shippingCompany'])),
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

        $order->load(['customer', 'product.category', 'product.fabric', 'tailor.tailorProfile.category', 'review', 'shippingCompany', 'trackingEvents']);

        return response()->json([
            'data' => new OrderResource($order),
        ]);
    }

    public function active(Request $request)
    {
        $orders = Order::query()
            ->where('customer_id', $request->user()->id)
            ->whereIn('status', OrderLifecycle::customerActiveStatuses())
            ->with(['tailor.tailorProfile.category', 'product.category', 'product.fabric', 'shippingCompany', 'trackingEvents'])
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
            ->with(['tailor.tailorProfile.category', 'product.category', 'product.fabric', 'review', 'shippingCompany', 'trackingEvents'])
            ->latest('updated_at')
            ->paginate(20);

        return OrderResource::collection($orders)->additional([
            'meta' => ['scope' => 'history'],
        ]);
    }

    public function purchased(Request $request)
    {
        $orders = Order::query()
            ->where('customer_id', $request->user()->id)
            ->with(['tailor.tailorProfile.category', 'product.category', 'product.fabric', 'review', 'shippingCompany', 'trackingEvents'])
            ->latest('updated_at')
            ->paginate(12);

        return OrderResource::collection($orders)->additional([
            'meta' => ['scope' => 'purchased'],
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
            'tracking_stage' => OrderTracking::STAGE_CANCELLED,
            'cancellation_reason' => $request->validated('reason'),
        ]);

        $this->trackingEventRecorder->record(
            $order,
            OrderTracking::STAGE_CANCELLED,
            OrderTracking::ROLE_CUSTOMER,
            $request->validated('reason'),
        );

        $order->refresh()->load(['customer', 'tailor', 'product.category', 'product.fabric', 'review', 'shippingCompany', 'trackingEvents']);

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

    /**
     * @param array<string, mixed> $configuration
     * @return array<string, mixed>
     */
    private function buildOrderConfiguration(Product $product, array $configuration): array
    {
        $payload = [];

        $selectedColorKey = filled($configuration['color'] ?? null)
            ? (string) $configuration['color']
            : null;

        if ($selectedColorKey !== null) {
            $colorOption = collect($product->localizedColorOptions())
                ->firstWhere('key', $selectedColorKey);

            if (is_array($colorOption)) {
                $payload['color'] = $colorOption;
            }
        }

        $selectedFabricKey = filled($configuration['fabric'] ?? null)
            ? (string) $configuration['fabric']
            : null;

        if ($selectedFabricKey !== null) {
            $fabricOption = collect($product->availableFabricOptions())
                ->firstWhere('key', $selectedFabricKey);

            if (is_array($fabricOption)) {
                $payload['fabric'] = $fabricOption;
            }
        }

        return $payload;
    }
}
