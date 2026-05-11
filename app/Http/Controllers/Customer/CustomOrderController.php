<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\RejectCustomOrderQuoteRequest;
use App\Http\Requests\Customer\StoreCustomOrderRequest;
use App\Http\Resources\CustomOrderResource;
use App\Models\CustomOrder;
use App\Models\CustomOrderImage;
use App\Models\Measurement;
use App\Services\OrderMatchingService;
use App\Services\TrackingEventRecorder;
use App\Support\OrderTracking;
use App\Support\Tailor\TailorOnboardingOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class CustomOrderController extends Controller
{
    public function __construct(
        private readonly OrderMatchingService $orderMatchingService,
        private readonly TrackingEventRecorder $trackingEventRecorder,
    ) {
    }

    public function metadata(): JsonResponse
    {
        $measurements = Measurement::query()
            ->active()
            ->ordered()
            ->get()
            ->map(fn (Measurement $measurement): array => [
                'id' => $measurement->id,
                'name' => $measurement->display_name,
                'slug' => $measurement->slug,
                'description' => $measurement->display_description,
                'helper_text' => $measurement->display_helper_text,
                'guide_text' => $measurement->display_guide_text,
                'guide_image_url' => filled($measurement->guide_image_path)
                    ? asset('storage/'.$measurement->guide_image_path)
                    : null,
            ])
            ->values()
            ->all();

        return response()->json([
            'data' => [
                'specialties' => TailorOnboardingOptions::SPECIALIZATIONS,
                'wilayas' => TailorOnboardingOptions::WILAYAS,
                'measurements' => $measurements,
            ],
        ]);
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'scope' => ['nullable', 'string', 'in:all,active,history'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $scope = $validated['scope'] ?? 'all';

        $query = CustomOrder::query()
            ->where('customer_id', $request->user()->id)
            ->with(['images', 'tailor', 'trackingEvents']);

        if ($scope === 'active') {
            $query->whereNotIn('status', $this->historyStatuses());
        }

        if ($scope === 'history') {
            $query->whereIn('status', $this->historyStatuses());
        }

        $orders = $query
            ->latest('updated_at')
            ->paginate($validated['per_page'] ?? 12);

        return CustomOrderResource::collection($orders)->additional([
            'meta' => [
                'scope' => $scope,
            ],
        ]);
    }

    public function show(CustomOrder $customOrder): JsonResponse
    {
        $this->authorize('view', $customOrder);

        $customOrder->load(['customer', 'tailor', 'images', 'trackingEvents']);

        return response()->json([
            'data' => new CustomOrderResource($customOrder),
        ]);
    }

    public function store(StoreCustomOrderRequest $request): JsonResponse
    {
        $this->authorize('create', CustomOrder::class);

        $customOrder = DB::transaction(function () use ($request): CustomOrder {
            $data = $request->validated();

            $customOrder = CustomOrder::query()->create([
                'customer_id' => $request->user()->id,
                'title' => $data['title'],
                'tailor_specialty' => $data['tailor_specialty'],
                'fabric_type' => $data['fabric_type'] ?? null,
                'measurements' => collect($data['measurements'] ?? [])
                    ->filter(fn ($value): bool => $value !== null && $value !== '')
                    ->map(fn ($value): float => round((float) $value, 2))
                    ->all(),
                'notes' => $data['notes'] ?? null,
                'delivery_latitude' => $data['customer_location']['latitude'],
                'delivery_longitude' => $data['customer_location']['longitude'],
                'delivery_work_wilaya' => $data['customer_location']['work_wilaya'],
                'status' => CustomOrder::STATUS_PLACED,
            ]);

            foreach ($request->file('reference_images', []) as $index => $image) {
                CustomOrderImage::query()->create([
                    'custom_order_id' => $customOrder->id,
                    'image_path' => $image->store("customers/{$request->user()->id}/custom-orders/{$customOrder->id}", 'public'),
                    'sort_order' => $index,
                ]);
            }

            return $customOrder;
        });

        $customOrder->load(['images', 'trackingEvents']);
        $this->trackingEventRecorder->seedInitialCustomOrderTimeline($customOrder);

        return response()->json([
            'message' => __('messages.custom_orders.created_success'),
            'data' => new CustomOrderResource($customOrder->fresh(['images', 'trackingEvents'])),
        ], 201);
    }

    public function acceptQuote(Request $request, CustomOrder $customOrder): JsonResponse
    {
        $this->authorize('respondToQuote', $customOrder);

        if ($customOrder->status !== CustomOrder::STATUS_QUOTED || $customOrder->quote_amount === null) {
            return response()->json([
                'message' => __('messages.custom_orders.quote_not_available'),
            ], 422);
        }

        $customOrder = DB::transaction(function () use ($customOrder): CustomOrder {
            $lockedOrder = CustomOrder::query()
                ->with(['images', 'trackingEvents'])
                ->whereKey($customOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->status !== CustomOrder::STATUS_QUOTED || $lockedOrder->quote_amount === null) {
                throw ValidationException::withMessages([
                    'quote' => [__('messages.custom_orders.quote_not_available')],
                ]);
            }

            $tailors = $this->orderMatchingService->findTailorsForRequirement(
                specialization: $lockedOrder->tailor_specialty,
                fallbackCategoryId: null,
                deliveryLatitude: $lockedOrder->delivery_latitude,
                deliveryLongitude: $lockedOrder->delivery_longitude,
                deliveryWilaya: $lockedOrder->delivery_work_wilaya,
            );

            $recommendedTailor = $tailors->first();
            $nextStatus = $recommendedTailor
                ? CustomOrder::STATUS_ASSIGNED_TO_TAILOR
                : CustomOrder::STATUS_TAILOR_ASSIGNMENT_PENDING;

            $lockedOrder->forceFill([
                'status' => $nextStatus,
                'tailor_id' => $recommendedTailor?->id,
                'accepted_at' => now(),
                'assigned_at' => $recommendedTailor ? now() : null,
                'assignment_meta' => [
                    'recommended_tailor_id' => $recommendedTailor?->id,
                    'eligible_tailors_count' => $tailors->count(),
                ],
            ])->save();

            return $lockedOrder->fresh(['images', 'tailor', 'trackingEvents']);
        });

        $this->trackingEventRecorder->record(
            $customOrder,
            OrderTracking::STAGE_QUOTE_ACCEPTED,
            OrderTracking::ROLE_CUSTOMER,
            __('messages.custom_orders.timeline.quote_accepted'),
        );

        $this->trackingEventRecorder->record(
            $customOrder,
            $customOrder->status,
            OrderTracking::ROLE_SYSTEM,
            $customOrder->status === CustomOrder::STATUS_ASSIGNED_TO_TAILOR
                ? __('messages.custom_orders.timeline.assigned_to_tailor')
                : __('messages.custom_orders.timeline.tailor_assignment_pending'),
        );

        return response()->json([
            'message' => __('messages.custom_orders.quote_accepted_success'),
            'data' => new CustomOrderResource($customOrder->fresh(['images', 'tailor', 'trackingEvents'])),
        ]);
    }

    public function rejectQuote(RejectCustomOrderQuoteRequest $request, CustomOrder $customOrder): JsonResponse
    {
        $this->authorize('respondToQuote', $customOrder);

        if ($customOrder->status !== CustomOrder::STATUS_QUOTED) {
            return response()->json([
                'message' => __('messages.custom_orders.quote_not_available'),
            ], 422);
        }

        $customOrder->forceFill([
            'status' => CustomOrder::STATUS_QUOTE_REJECTED,
            'quote_rejection_note' => $request->validated('note'),
        ])->save();

        $this->trackingEventRecorder->record(
            $customOrder,
            OrderTracking::STAGE_QUOTE_REJECTED,
            OrderTracking::ROLE_CUSTOMER,
            $request->validated('note'),
        );

        return response()->json([
            'message' => __('messages.custom_orders.quote_rejected_success'),
            'data' => new CustomOrderResource($customOrder->fresh(['images', 'tailor', 'trackingEvents'])),
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function historyStatuses(): array
    {
        return [
            CustomOrder::STATUS_QUOTE_REJECTED,
            CustomOrder::STATUS_RECEIVED,
            CustomOrder::STATUS_DELIVERED,
            CustomOrder::STATUS_CANCELLED,
        ];
    }
}
