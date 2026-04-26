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
        $statsService = app(AdminDashboardStatsService::class);
        $stats = $statsService->getKpis();
        $ordersTrend = $statsService->getOrdersTrend(7)['values'];
        $usersTrend = $statsService->getUserRegistrationsTrend(7)['values'];
        $revenueValue = $stats['total_revenue'] !== null
            ? number_format($stats['total_revenue'], 2) . ' MAD'
            : 'N/A';
        $lowStockValue = $stats['low_stock_products'] !== null
            ? (string) $stats['low_stock_products']
            : 'N/A';

        return [
            Stat::make('Total Orders', (string) $stats['total_orders'])
                ->description('All tailoring requests')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->chart($ordersTrend)
                ->color('primary'),
            Stat::make('New Orders', (string) $stats['new_orders'])
                ->description('Freshly submitted requests')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('In Progress', (string) $stats['in_progress_orders'])
                ->description('Accepted and being crafted')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info'),
            Stat::make('Completed Orders', (string) $stats['completed_orders'])
                ->description('Delivered successfully')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Cancelled Orders', (string) $stats['cancelled_orders'])
                ->description('Customer / tailor cancellations')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
            Stat::make('Total Customers', (string) $stats['total_customers'])
                ->description('Registered customer accounts')
                ->descriptionIcon('heroicon-m-users')
                ->chart($usersTrend)
                ->color('primary'),
            Stat::make('Total Tailors', (string) $stats['total_vendors'])
                ->description('Service provider accounts')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
            Stat::make('Revenue (Completed)', $revenueValue)
                ->description('Completed order value')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Low Stock Alerts', $lowStockValue)
                ->description('Inventory threshold monitoring')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($stats['low_stock_products'] !== null ? 'danger' : 'gray'),
        ];
    }
}
