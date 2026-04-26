<?php

namespace App\Filament\Widgets;

use App\Services\Admin\AdminDashboardStatsService;
use Filament\Widgets\ChartWidget;

class MostRequestedServicesChart extends ChartWidget
{
    protected static ?string $heading = 'Most Requested Services';

    protected static ?string $description = 'Top categories by number of orders';

    protected static string $color = 'warning';

    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = [
        'default' => 1,
        'xl' => 4,
    ];

    protected static ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $series = app(AdminDashboardStatsService::class)->getMostRequestedCategories();

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $series['values'],
                    'backgroundColor' => [
                        '#D97706',
                        '#A16207',
                        '#92400E',
                        '#CA8A04',
                        '#B45309',
                        '#F59E0B',
                    ],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $series['labels'],
        ];
    }
}
