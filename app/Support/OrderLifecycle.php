<?php

namespace App\Support;

use App\Models\Order;

class OrderLifecycle
{
    /**
     * @return array<string, array<int, string>>
     */
    public static function tailorTransitions(): array
    {
        return [
            Order::STATUS_ACCEPTED => [Order::STATUS_PROCESSING],
            Order::STATUS_PROCESSING => [Order::STATUS_READY_FOR_DELIVERY],
            Order::STATUS_READY_FOR_DELIVERY => [Order::STATUS_COMPLETED],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function customerCancellableStatuses(): array
    {
        return [
            Order::STATUS_SEARCHING_FOR_TAILOR,
            Order::STATUS_ACCEPTED,
            Order::STATUS_PROCESSING,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function tailorCancellableStatuses(): array
    {
        return [
            Order::STATUS_ACCEPTED,
            Order::STATUS_PROCESSING,
            Order::STATUS_READY_FOR_DELIVERY,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function customerActiveStatuses(): array
    {
        return [
            Order::STATUS_SEARCHING_FOR_TAILOR,
            Order::STATUS_ACCEPTED,
            Order::STATUS_PROCESSING,
            Order::STATUS_READY_FOR_DELIVERY,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function customerHistoryStatuses(): array
    {
        return [
            Order::STATUS_COMPLETED,
            Order::STATUS_CANCELLED,
            Order::STATUS_CANCELLED_BY_CUSTOMER,
            Order::STATUS_CANCELLED_BY_TAILOR,
            Order::STATUS_NO_TAILORS_AVAILABLE,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function tailorActiveStatuses(): array
    {
        return [
            Order::STATUS_ACCEPTED,
            Order::STATUS_PROCESSING,
            Order::STATUS_READY_FOR_DELIVERY,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function tailorHistoryStatuses(): array
    {
        return [
            Order::STATUS_COMPLETED,
            Order::STATUS_CANCELLED,
            Order::STATUS_CANCELLED_BY_CUSTOMER,
            Order::STATUS_CANCELLED_BY_TAILOR,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function terminalStatuses(): array
    {
        return [
            Order::STATUS_COMPLETED,
            Order::STATUS_CANCELLED,
            Order::STATUS_CANCELLED_BY_CUSTOMER,
            Order::STATUS_CANCELLED_BY_TAILOR,
            Order::STATUS_NO_TAILORS_AVAILABLE,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function allowedTailorNextStatuses(string $currentStatus): array
    {
        return self::tailorTransitions()[$currentStatus] ?? [];
    }

    public static function canTailorTransition(string $currentStatus, string $nextStatus): bool
    {
        return in_array($nextStatus, self::allowedTailorNextStatuses($currentStatus), true);
    }

    /**
     * @return array<string, string>
     */
    public static function realtimeEventByAction(): array
    {
        return [
            'created' => 'order.created',
            'accepted' => 'order.accepted',
            'status_updated' => 'order.status_updated',
            'cancelled_by_customer' => 'order.cancelled_by_customer',
            'cancelled_by_tailor' => 'order.cancelled_by_tailor',
        ];
    }
}
