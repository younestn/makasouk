<?php

namespace App\Http\Controllers\Tailor;

use App\Events\OrderAccepted;
use App\Events\OrderCancelledByTailor;
use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tailor\AcceptOrderRequest;
use App\Http\Requests\Tailor\CancelOrderRequest;
use App\Http\Requests\Tailor\DeclineOrderOfferRequest;
use App\Http\Requests\Tailor\NotMySpecialtyRequest;
use App\Http\Requests\Tailor\UpdateOrderStatusRequest;
use App\Http\Requests\Tailor\UpdateOrderTrackingStageRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\TailorOrderOffer;
use App\Services\OrderMatchingService;
use App\Services\TailorScoringService;
use App\Services\TrackingEventRecorder;
use App\Support\OrderLifecycle;
use App\Support\OrderTracking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderMatchingService $orderMatchingService,
        private readonly TailorScoringService $tailorScoringService,
        private readonly TrackingEventRecorder $trackingEventRecorder,
    )
    {
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order->load(['customer', 'tailor.tailorProfile.category', 'product.category', 'product.fabric', 'review', 'trackingEvents']);

        if ($request->user()?->role === \App\Models\User::ROLE_TAILOR) {
            $offer = TailorOrderOffer::query()
                ->where('order_id', $order->id)
                ->where('tailor_id', $request->user()->id)
                ->first();

            if ($offer && $offer->read_at === null) {
                $offer->forceFill([
                    'status' => TailorOrderOffer::STATUS_READ,
                    'read_at' => now(),
                ])->save();
            }

            $order->setRelation('tailorOffers', new EloquentCollection($offer ? [$offer] : []));
        }

        return response()->json([
            'data' => new OrderResource($order),
        ]);
    }

    public function offers(Request $request)
    {
        $orders = Order::query()
            ->whereHas('tailorOffers', fn ($query) => $query
                ->where('tailor_id', $request->user()->id)
                ->whereIn('status', [TailorOrderOffer::STATUS_UNREAD, TailorOrderOffer::STATUS_READ]))
            ->with([
                'customer',
                'product.category',
                'product.fabric',
                'tailorOffers' => fn ($query) => $query->where('tailor_id', $request->user()->id),
            ])
            ->latest('created_at')
            ->paginate(20);

        return OrderResource::collection($orders)->additional([
            'meta' => [
                'scope' => 'offers',
                'unread_count' => TailorOrderOffer::query()
                    ->where('tailor_id', $request->user()->id)
                    ->whereNull('read_at')
                    ->whereIn('status', [TailorOrderOffer::STATUS_UNREAD, TailorOrderOffer::STATUS_READ])
                    ->count(),
            ],
        ]);
    }

    public function acceptOrder(AcceptOrderRequest $request, Order $order): JsonResponse
    {
        $this->authorize('acceptByTailor', $order);

        /** @var \App\Models\User $tailor */
        $tailor = $request->user();

        if (! $this->orderMatchingService->isTailorEligibleForOrder($tailor, $order)) {
            return response()->json([
                'message' => __('messages.orders.tailor_not_eligible'),
            ], 422);
        }

        $tailorId = (int) $tailor->id;

        $result = DB::transaction(function () use ($order, $tailorId): array {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->tailor_id !== null) {
                TailorOrderOffer::query()
                    ->where('order_id', $lockedOrder->id)
                    ->where('tailor_id', $tailorId)
                    ->whereIn('status', [TailorOrderOffer::STATUS_UNREAD, TailorOrderOffer::STATUS_READ])
                    ->update([
                        'status' => TailorOrderOffer::STATUS_TAKEN,
                        'responded_at' => now(),
                        'updated_at' => now(),
                    ]);

                return [
                    'success' => false,
                    'status' => 409,
                    'message' => __('messages.orders.already_accepted'),
                    'order' => $lockedOrder,
                ];
            }

            $lockedOrder->update([
                'tailor_id' => $tailorId,
                'status' => Order::STATUS_ACCEPTED,
                'tracking_stage' => OrderTracking::STAGE_ASSIGNED_TO_TAILOR,
                'accepted_at' => now(),
            ]);

            TailorOrderOffer::query()
                ->where('order_id', $lockedOrder->id)
                ->where('tailor_id', $tailorId)
                ->update([
                    'status' => TailorOrderOffer::STATUS_ACCEPTED,
                    'read_at' => now(),
                    'responded_at' => now(),
                    'updated_at' => now(),
                ]);

            TailorOrderOffer::query()
                ->where('order_id', $lockedOrder->id)
                ->where('tailor_id', '!=', $tailorId)
                ->whereIn('status', [TailorOrderOffer::STATUS_UNREAD, TailorOrderOffer::STATUS_READ])
                ->update([
                    'status' => TailorOrderOffer::STATUS_TAKEN,
                    'responded_at' => now(),
                    'updated_at' => now(),
                ]);

            return [
                'success' => true,
                'status' => 200,
                'message' => __('messages.orders.accepted_success'),
                'order' => $lockedOrder->fresh(['customer', 'tailor', 'product.category', 'product.fabric']),
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
        $this->trackingEventRecorder->record(
            $result['order'],
            OrderTracking::STAGE_ASSIGNED_TO_TAILOR,
            OrderTracking::ROLE_TAILOR,
            __('messages.orders.timeline.assigned_to_tailor'),
        );
        $this->tailorScoringService->record($tailor, TailorScoringService::EVENT_ACCEPTED, $result['order']);

        return response()->json([
            'message' => $result['message'],
            'data' => new OrderResource($result['order']),
        ]);
    }

    public function decline(DeclineOrderOfferRequest $request, Order $order): JsonResponse
    {
        return $this->recordOfferDecision(
            request: $request,
            order: $order,
            status: TailorOrderOffer::STATUS_REJECTED,
            reason: $request->validated('reason'),
            note: $request->validated('note'),
            scoreEvent: TailorScoringService::EVENT_REJECTED,
            message: __('messages.orders.offer_rejected_success'),
        );
    }

    public function notMySpecialty(NotMySpecialtyRequest $request, Order $order): JsonResponse
    {
        return $this->recordOfferDecision(
            request: $request,
            order: $order,
            status: TailorOrderOffer::STATUS_NOT_MY_SPECIALTY,
            reason: TailorOrderOffer::REASON_NOT_MY_SPECIALTY,
            note: $request->validated('note'),
            scoreEvent: TailorScoringService::EVENT_NOT_MY_SPECIALTY,
            message: __('messages.orders.offer_not_my_specialty_success'),
        );
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $this->authorize('updateByTailor', $order);

        $nextStatus = $request->validated('status');
        $currentStatus = (string) $order->status;

        if (! OrderLifecycle::canTailorTransition($currentStatus, $nextStatus)) {
            return response()->json([
                'message' => __('messages.orders.status_transition_not_allowed'),
                'meta' => [
                    'current_status' => $currentStatus,
                    'allowed_next_statuses' => OrderLifecycle::allowedTailorNextStatuses($currentStatus),
                ],
            ], 422);
        }

        $defaultTrackingStage = OrderTracking::defaultOrderStageForStatus($order->forceFill(['status' => $nextStatus]));

        $order->update([
            'status' => $nextStatus,
            'tracking_stage' => $defaultTrackingStage ?: $order->tracking_stage,
        ]);

        if ($defaultTrackingStage !== null) {
            $this->trackingEventRecorder->record(
                $order,
                $defaultTrackingStage,
                OrderTracking::ROLE_TAILOR,
                __('messages.orders.timeline.'.$defaultTrackingStage),
            );
        }

        $order->refresh()->load(['customer', 'tailor', 'product.category', 'product.fabric', 'review', 'trackingEvents']);

        if ($nextStatus === Order::STATUS_COMPLETED && $order->tailor_id !== null) {
            $this->tailorScoringService->record((int) $order->tailor_id, TailorScoringService::EVENT_COMPLETED, $order);
        }

        Event::dispatch(new OrderStatusUpdated($order));

        return response()->json([
            'message' => __('messages.orders.status_updated_success'),
            'data' => new OrderResource($order),
        ]);
    }

    public function updateTrackingStage(UpdateOrderTrackingStageRequest $request, Order $order): JsonResponse
    {
        $this->authorize('updateByTailor', $order);

        $stage = $request->validated('stage');
        $allowedStages = OrderTracking::allowedTailorTrackingStagesForOrder($order);

        if (! in_array($stage, $allowedStages, true)) {
            return response()->json([
                'message' => __('messages.orders.tracking_stage_not_allowed'),
                'meta' => [
                    'allowed_stages' => $allowedStages,
                ],
            ], 422);
        }

        $impliedStatus = OrderTracking::impliedOrderStatusForStage($stage);

        $order->forceFill([
            'tracking_stage' => $stage,
            'status' => $impliedStatus ?? $order->status,
        ])->save();

        $this->trackingEventRecorder->record(
            $order,
            $stage,
            OrderTracking::ROLE_TAILOR,
            $request->validated('description') ?: __('messages.orders.timeline.'.$stage),
        );

        $order->refresh()->load(['customer', 'tailor', 'product.category', 'product.fabric', 'review', 'trackingEvents']);

        if ($order->status === Order::STATUS_COMPLETED && $order->tailor_id !== null) {
            $this->tailorScoringService->record((int) $order->tailor_id, TailorScoringService::EVENT_COMPLETED, $order);
        }

        Event::dispatch(new OrderStatusUpdated($order));

        return response()->json([
            'message' => __('messages.orders.status_updated_success'),
            'data' => new OrderResource($order),
        ]);
    }

    public function cancel(CancelOrderRequest $request, Order $order): JsonResponse
    {
        $this->authorize('cancelByTailor', $order);

        if (! in_array($order->status, OrderLifecycle::tailorCancellableStatuses(), true)) {
            return response()->json([
                'message' => __('messages.orders.cannot_cancel_status'),
                'meta' => [
                    'current_status' => $order->status,
                    'allowed_cancel_statuses' => OrderLifecycle::tailorCancellableStatuses(),
                ],
            ], 422);
        }

        $order->update([
            'status' => Order::STATUS_CANCELLED_BY_TAILOR,
            'tracking_stage' => OrderTracking::STAGE_CANCELLED,
            'cancellation_reason' => $request->validated('reason'),
        ]);

        $this->tailorScoringService->record((int) $order->tailor_id, TailorScoringService::EVENT_ACCEPTED_THEN_CANCELLED, $order, $request->validated('reason'));

        $this->trackingEventRecorder->record(
            $order,
            OrderTracking::STAGE_CANCELLED,
            OrderTracking::ROLE_TAILOR,
            $request->validated('reason'),
        );

        $order->refresh()->load(['customer', 'tailor', 'product.category', 'product.fabric', 'review', 'trackingEvents']);

        Event::dispatch(new OrderCancelledByTailor($order));

        return response()->json([
            'message' => __('messages.orders.cancelled_by_tailor'),
            'data' => new OrderResource($order),
        ]);
    }

    public function active(Request $request)
    {
        $orders = Order::query()
            ->where('tailor_id', $request->user()->id)
            ->whereIn('status', OrderLifecycle::tailorActiveStatuses())
            ->with(['customer', 'product.category', 'product.fabric', 'trackingEvents'])
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
            ->with(['customer', 'product.category', 'product.fabric', 'review', 'trackingEvents'])
            ->latest('updated_at')
            ->paginate(20);

        return OrderResource::collection($orders)->additional([
            'meta' => ['scope' => 'history'],
        ]);
    }

    private function recordOfferDecision(
        Request $request,
        Order $order,
        string $status,
        string $reason,
        ?string $note,
        string $scoreEvent,
        string $message,
    ): JsonResponse {
        $this->authorize('acceptByTailor', $order);

        /** @var \App\Models\User $tailor */
        $tailor = $request->user();

        $offer = TailorOrderOffer::query()
            ->where('order_id', $order->id)
            ->where('tailor_id', $tailor->id)
            ->first();

        if (! $offer) {
            return response()->json([
                'message' => __('messages.orders.offer_not_found'),
            ], 404);
        }

        if ($order->tailor_id !== null) {
            $offer->forceFill([
                'status' => TailorOrderOffer::STATUS_TAKEN,
                'responded_at' => now(),
            ])->save();

            return response()->json([
                'message' => __('messages.orders.already_accepted'),
                'data' => new OrderResource($order->fresh(['customer', 'tailor', 'product.category', 'product.fabric'])),
            ], 409);
        }

        $offer->forceFill([
            'status' => $status,
            'reason' => $reason,
            'note' => $note,
            'read_at' => $offer->read_at ?? now(),
            'responded_at' => now(),
        ])->save();

        $this->tailorScoringService->record($tailor, $scoreEvent, $order, $note);

        $freshOrder = $order->fresh(['customer', 'product.category', 'product.fabric']);
        $freshOrder->setRelation('tailorOffers', new EloquentCollection([$offer->fresh()]));

        return response()->json([
            'message' => $message,
            'data' => new OrderResource($freshOrder),
        ]);
    }
}
