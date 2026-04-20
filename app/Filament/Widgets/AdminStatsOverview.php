<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\TailorProfile;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', (string) User::query()->count()),
            Stat::make('Customers', (string) User::query()->where('role', User::ROLE_CUSTOMER)->count()),
            Stat::make('Tailors', (string) User::query()->where('role', User::ROLE_TAILOR)->count()),
            Stat::make('Pending Tailors', (string) User::query()->where('role', User::ROLE_TAILOR)->whereNull('approved_at')->count()),
            Stat::make('Total Orders', (string) Order::query()->count()),
            Stat::make('Completed Orders', (string) Order::query()->where('status', Order::STATUS_COMPLETED)->count()),
            Stat::make('Cancelled Orders', (string) Order::query()->whereIn('status', [Order::STATUS_CANCELLED, Order::STATUS_CANCELLED_BY_CUSTOMER, Order::STATUS_CANCELLED_BY_TAILOR])->count()),
            Stat::make('Avg Tailor Rating', (string) number_format((float) TailorProfile::query()->avg('average_rating'), 2)),
        ];
    }
}
