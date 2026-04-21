<?php

namespace App\Filament\Widgets;

use App\Services\Admin\AdminDashboardStatsService;
use Filament\Widgets\ChartWidget;

class OrdersTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Orders Trend';

    protected static ?string $description = 'Daily orders for the selected period';

    protected static string $color = 'primary';

    protected int | string | array $columnSpan = [
        'default' => 1,
        'xl' => 4,
    ];

    protected static ?string $maxHeight = '280px';

    protected static ?string $pollingInterval = '90s';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return [
            7 => 'Last 7 days',
            14 => 'Last 14 days',
            30 => 'Last 30 days',
        ];
    }

    protected function getData(): array
    {
        $days = (int) ($this->filter ?? 14);
        $series = app(AdminDashboardStatsService::class)->getOrdersTrend($days);

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $series['values'],
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $series['labels'],
        ];
    }
}
