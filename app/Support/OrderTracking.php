<?php

namespace App\Support;

use App\Models\CustomOrder;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class OrderTracking
{
    public const ROLE_SYSTEM = 'system';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_TAILOR = 'tailor';
    public const ROLE_CUSTOMER = 'customer';

    public const STAGE_PLACED = 'placed';
    public const STAGE_ADMIN_REVIEW = 'admin_review';
    public const STAGE_TAILOR_ASSIGNMENT_PENDING = 'tailor_assignment_pending';
    public const STAGE_ASSIGNED_TO_TAILOR = 'assigned_to_tailor';
    public const STAGE_WORK_STARTED = 'work_started';
    public const STAGE_CUTTING_STARTED = 'cutting_started';
    public const STAGE_SEWING_STARTED = 'sewing_started';
    public const STAGE_PRODUCT_COMPLETED = 'product_completed';
    public const STAGE_COMPLETED = 'completed';
    public const STAGE_PREPARING = 'preparing';
    public const STAGE_SENT_TO_SHIPPING_CENTER = 'sent_to_shipping_center';
    public const STAGE_ARRIVED = 'arrived';
    public const STAGE_DELIVERED = 'delivered';
    public const STAGE_RECEIVED = 'received';
    public const STAGE_QUOTED = 'quoted';
    public const STAGE_QUOTE_ACCEPTED = 'quote_accepted';
    public const STAGE_QUOTE_REJECTED = 'quote_rejected';
    public const STAGE_CANCELLED = 'cancelled';

    /**
     * @return array<int, string>
     */
    public static function orderRoadmap(): array
    {
        return [
            self::STAGE_PLACED,
            self::STAGE_ADMIN_REVIEW,
            self::STAGE_TAILOR_ASSIGNMENT_PENDING,
            self::STAGE_ASSIGNED_TO_TAILOR,
            self::STAGE_WORK_STARTED,
            self::STAGE_CUTTING_STARTED,
            self::STAGE_SEWING_STARTED,
            self::STAGE_PRODUCT_COMPLETED,
            self::STAGE_PREPARING,
            self::STAGE_SENT_TO_SHIPPING_CENTER,
            self::STAGE_ARRIVED,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function customOrderRoadmap(): array
    {
        return [
            self::STAGE_PLACED,
            self::STAGE_ADMIN_REVIEW,
            self::STAGE_QUOTED,
            self::STAGE_QUOTE_ACCEPTED,
            self::STAGE_TAILOR_ASSIGNMENT_PENDING,
            self::STAGE_ASSIGNED_TO_TAILOR,
            self::STAGE_WORK_STARTED,
            self::STAGE_CUTTING_STARTED,
            self::STAGE_SEWING_STARTED,
            self::STAGE_PRODUCT_COMPLETED,
            self::STAGE_COMPLETED,
            self::STAGE_PREPARING,
            self::STAGE_SENT_TO_SHIPPING_CENTER,
            self::STAGE_ARRIVED,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function allowedTailorTrackingStagesForOrder(Order $order): array
    {
        return match ((string) $order->status) {
            Order::STATUS_ACCEPTED => [
                self::STAGE_ASSIGNED_TO_TAILOR,
                self::STAGE_WORK_STARTED,
            ],
            Order::STATUS_PROCESSING => [
                self::STAGE_WORK_STARTED,
                self::STAGE_CUTTING_STARTED,
                self::STAGE_SEWING_STARTED,
                self::STAGE_PRODUCT_COMPLETED,
            ],
            Order::STATUS_READY_FOR_DELIVERY => [
                self::STAGE_PREPARING,
                self::STAGE_SENT_TO_SHIPPING_CENTER,
                self::STAGE_ARRIVED,
            ],
            Order::STATUS_COMPLETED => [
                self::STAGE_RECEIVED,
            ],
            default => [],
        };
    }

    public static function defaultOrderStageForStatus(Order $order): ?string
    {
        return match ((string) $order->status) {
            Order::STATUS_SEARCHING_FOR_TAILOR, Order::STATUS_NO_TAILORS_AVAILABLE => self::STAGE_TAILOR_ASSIGNMENT_PENDING,
            Order::STATUS_ACCEPTED => self::STAGE_ASSIGNED_TO_TAILOR,
            Order::STATUS_PROCESSING => self::STAGE_WORK_STARTED,
            Order::STATUS_READY_FOR_DELIVERY => self::STAGE_PREPARING,
            Order::STATUS_COMPLETED => self::STAGE_RECEIVED,
            Order::STATUS_CANCELLED, Order::STATUS_CANCELLED_BY_CUSTOMER, Order::STATUS_CANCELLED_BY_TAILOR => self::STAGE_CANCELLED,
            default => null,
        };
    }

    public static function canReviewOrder(Order $order): bool
    {
        return $order->status === Order::STATUS_COMPLETED
            || in_array((string) $order->tracking_stage, [self::STAGE_RECEIVED], true);
    }

    public static function impliedOrderStatusForStage(string $stage): ?string
    {
        return match ($stage) {
            self::STAGE_ASSIGNED_TO_TAILOR => Order::STATUS_ACCEPTED,
            self::STAGE_WORK_STARTED,
            self::STAGE_CUTTING_STARTED,
            self::STAGE_SEWING_STARTED,
            self::STAGE_PRODUCT_COMPLETED => Order::STATUS_PROCESSING,
            self::STAGE_PREPARING,
            self::STAGE_SENT_TO_SHIPPING_CENTER,
            self::STAGE_ARRIVED => Order::STATUS_READY_FOR_DELIVERY,
            self::STAGE_RECEIVED => Order::STATUS_COMPLETED,
            self::STAGE_CANCELLED => Order::STATUS_CANCELLED,
            default => null,
        };
    }

    /**
     * @param  Collection<int, \App\Models\TrackingEvent>|array<int, \App\Models\TrackingEvent>  $events
     * @return array<int, array<string, mixed>>
     */
    public static function buildTimeline(Model $trackable, Collection|array $events, ?string $currentCode = null): array
    {
        $roadmap = $trackable instanceof CustomOrder
            ? self::customOrderRoadmap()
            : self::orderRoadmap();

        $eventsCollection = $events instanceof Collection ? $events : collect($events);
        $eventMap = $eventsCollection
            ->filter(fn ($event): bool => filled($event->code ?? null))
            ->keyBy(fn ($event): string => (string) $event->code);

        $currentCode ??= $trackable instanceof CustomOrder
            ? (string) $trackable->status
            : ((string) $trackable->tracking_stage ?: self::defaultOrderStageForStatus($trackable));

        $currentIndex = self::stageIndex($currentCode, $roadmap);

        $timeline = collect($roadmap)
            ->map(function (string $code, int $index) use ($eventMap, $currentIndex, $currentCode): array {
                $event = $eventMap->get($code);
                $state = 'pending';

                if ($event !== null || ($currentIndex !== null && $index < $currentIndex)) {
                    $state = 'completed';
                }

                if ($currentCode === $code) {
                    $state = 'current';
                }

                return [
                    'code' => $code,
                    'state' => $state,
                    'occurred_at' => optional($event?->occurred_at ?: $event?->created_at)?->toISOString(),
                    'description' => $event?->description,
                    'responsible_role' => $event?->responsible_role,
                    'meta' => $event?->meta ?? [],
                ];
            })
            ->values();

        foreach (self::branchTerminalStages($trackable) as $branchStage) {
            if ($currentCode !== $branchStage && ! $eventMap->has($branchStage)) {
                continue;
            }

            $event = $eventMap->get($branchStage);

            $timeline->push([
                'code' => $branchStage,
                'state' => $currentCode === $branchStage ? 'current' : 'completed',
                'occurred_at' => optional($event?->occurred_at ?: $event?->created_at)?->toISOString(),
                'description' => $event?->description,
                'responsible_role' => $event?->responsible_role,
                'meta' => $event?->meta ?? [],
            ]);
        }

        return $timeline->all();
    }

    /**
     * @param  array<int, string>  $roadmap
     */
    private static function stageIndex(?string $code, array $roadmap): ?int
    {
        if (! filled($code)) {
            return null;
        }

        $index = array_search($code, $roadmap, true);

        return $index === false ? null : $index;
    }

    /**
     * @return array<int, string>
     */
    private static function branchTerminalStages(Model $trackable): array
    {
        if ($trackable instanceof CustomOrder) {
            return [
                self::STAGE_QUOTE_REJECTED,
                self::STAGE_DELIVERED,
                self::STAGE_RECEIVED,
                self::STAGE_CANCELLED,
            ];
        }

        return [
            self::STAGE_RECEIVED,
            self::STAGE_CANCELLED,
        ];
    }
}
