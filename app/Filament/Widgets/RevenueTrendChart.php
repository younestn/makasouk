<?php

namespace App\Filament\Widgets;

use App\Services\Admin\AdminDashboardStatsService;
use Filament\Widgets\ChartWidget;

class RevenueTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Revenue Trend';

    protected static ?string $description = 'Completed order revenue (MAD)';

    protected static string $color = 'success';

    protected int | string | array $columnSpan = [
        'default' => 1,
        'xl' => 4,
    ];

    protected static ?string $maxHeight = '280px';

    protected static ?string $pollingInterval = '90s';

    protected function getType(): string
    {
        return 'bar';
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
        $series = app(AdminDashboardStatsService::class)->getRevenueTrend($days);

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (MAD)',
                    'data' => $series['values'],
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $series['labels'],
        ];
    }
}
