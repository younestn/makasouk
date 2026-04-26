<?php

namespace App\Support\Filament;

use App\Models\Order;
use App\Models\TailorProfile;
use App\Models\User;

class AdminUiState
{
    public static function orderStatusLabel(?string $status): string
    {
        return match ($status) {
            Order::STATUS_PENDING => __('admin.status.pending'),
            Order::STATUS_SEARCHING_FOR_TAILOR => __('admin.status.searching_for_tailor'),
            Order::STATUS_NO_TAILORS_AVAILABLE => __('admin.status.no_tailors_available'),
            Order::STATUS_ACCEPTED => __('admin.status.accepted'),
            Order::STATUS_PROCESSING => __('admin.status.processing'),
            Order::STATUS_READY_FOR_DELIVERY => __('admin.status.ready_for_delivery'),
            Order::STATUS_COMPLETED => __('admin.status.completed'),
            Order::STATUS_CANCELLED_BY_CUSTOMER => __('admin.status.cancelled_by_customer'),
            Order::STATUS_CANCELLED_BY_TAILOR => __('admin.status.cancelled_by_tailor'),
            Order::STATUS_CANCELLED => __('admin.status.cancelled'),
            default => (string) str($status ?? 'unknown')->headline(),
        };
    }

    public static function orderStatusColor(?string $status): string
    {
        return match ($status) {
            Order::STATUS_COMPLETED => 'success',
            Order::STATUS_ACCEPTED, Order::STATUS_PROCESSING, Order::STATUS_READY_FOR_DELIVERY => 'info',
            Order::STATUS_CANCELLED_BY_CUSTOMER, Order::STATUS_CANCELLED_BY_TAILOR, Order::STATUS_CANCELLED => 'danger',
            default => 'warning',
        };
    }

    public static function orderStatusIcon(?string $status): string
    {
        return match ($status) {
            Order::STATUS_COMPLETED => 'heroicon-m-check-circle',
            Order::STATUS_ACCEPTED, Order::STATUS_PROCESSING, Order::STATUS_READY_FOR_DELIVERY => 'heroicon-m-arrow-path',
            Order::STATUS_CANCELLED_BY_CUSTOMER, Order::STATUS_CANCELLED_BY_TAILOR, Order::STATUS_CANCELLED => 'heroicon-m-x-circle',
            default => 'heroicon-m-clock',
        };
    }

    public static function userRoleLabel(?string $role): string
    {
        return match ($role) {
            User::ROLE_ADMIN => __('admin.roles.admin'),
            User::ROLE_CUSTOMER => __('admin.roles.customer'),
            User::ROLE_TAILOR => __('admin.roles.tailor'),
            default => (string) str($role ?? 'unknown')->headline(),
        };
    }

    public static function userRoleColor(?string $role): string
    {
        return match ($role) {
            User::ROLE_ADMIN => 'primary',
            User::ROLE_CUSTOMER => 'success',
            User::ROLE_TAILOR => 'warning',
            default => 'gray',
        };
    }

    public static function toggleStatusLabel(bool $state, string $active = 'Active', string $inactive = 'Inactive'): string
    {
        if ($active === 'Active' && $inactive === 'Inactive') {
            return $state ? __('admin.status.active') : __('admin.status.inactive');
        }

        return $state ? $active : $inactive;
    }

    public static function toggleStatusColor(bool $state, string $active = 'success', string $inactive = 'gray'): string
    {
        return $state ? $active : $inactive;
    }

    public static function tailorProfileStatusLabel(?string $status): string
    {
        return match ($status) {
            TailorProfile::STATUS_ONLINE => __('admin.status.online'),
            TailorProfile::STATUS_OFFLINE, null => __('admin.status.offline'),
            default => (string) str($status)->headline(),
        };
    }

    public static function tailorProfileStatusColor(?string $status): string
    {
        return match ($status) {
            TailorProfile::STATUS_ONLINE => 'success',
            default => 'gray',
        };
    }

    public static function stockLevelLabel(?int $stock): string
    {
        if ($stock === null) {
            return __('admin.status.unknown');
        }

        if ($stock <= 0) {
            return __('admin.status.out_of_stock');
        }

        if ($stock <= 5) {
            return __('admin.status.low_stock');
        }

        return __('admin.status.in_stock');
    }

    public static function stockLevelColor(?int $stock): string
    {
        if ($stock === null) {
            return 'gray';
        }

        if ($stock <= 0) {
            return 'danger';
        }

        if ($stock <= 5) {
            return 'warning';
        }

        return 'success';
    }
}
