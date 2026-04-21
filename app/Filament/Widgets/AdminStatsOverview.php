<?php

namespace App\Filament\Widgets;

use App\Services\Admin\AdminDashboardStatsService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $stats = app(AdminDashboardStatsService::class)->getKpis();
        $ordersTrend = app(AdminDashboardStatsService::class)->getOrdersTrend(7)['values'];
        $usersTrend = app(AdminDashboardStatsService::class)->getUserRegistrationsTrend(7)['values'];
        $revenueValue = $stats['total_revenue'] !== null
            ? number_format($stats['total_revenue'], 2) . ' MAD'
            : 'N/A';
        $lowStockValue = $stats['low_stock_products'] !== null
            ? (string) $stats['low_stock_products']
            : 'N/A';

        return [
            Stat::make('Total Users', (string) $stats['total_users'])
                ->description('All registered accounts')
                ->descriptionIcon('heroicon-m-users')
                ->chart($usersTrend)
                ->color('primary'),
            Stat::make('Vendors / Tailors', (string) $stats['total_vendors'])
                ->description('Approved + pending tailor accounts')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
            Stat::make('Products', (string) $stats['total_products'])
                ->description('Active and draft products')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success'),
            Stat::make('Categories', (string) $stats['total_categories'])
                ->description('Catalog taxonomy')
                ->descriptionIcon('heroicon-m-tag')
                ->color('gray'),
            Stat::make('Total Orders', (string) $stats['total_orders'])
                ->description('Across all statuses')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->chart($ordersTrend)
                ->color('primary'),
            Stat::make('Pending Orders', (string) $stats['pending_orders'])
                ->description('Need operations follow-up')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Completed Orders', (string) $stats['completed_orders'])
                ->description('Fulfilled successfully')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Revenue (Completed)', $revenueValue)
                ->description('Computed from completed order products')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Low Stock Products', $lowStockValue)
                ->description('N/A when inventory columns are absent')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($stats['low_stock_products'] !== null ? 'danger' : 'gray'),
            Stat::make('Recent Registrations', (string) $stats['recent_registrations'])
                ->description('New users in the last 7 days')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info'),
        ];
    }
}
