<?php

namespace App\Filament\Widgets;

use App\Services\Admin\AdminDashboardStatsService;
use Filament\Widgets\ChartWidget;

class UserRegistrationsTrendChart extends ChartWidget
{
    protected static ?string $heading = 'User Registrations';

    protected static ?string $description = 'New users created over time';

    protected static string $color = 'info';

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
        $series = app(AdminDashboardStatsService::class)->getUserRegistrationsTrend($days);

        return [
            'datasets' => [
                [
                    'label' => 'Registrations',
                    'data' => $series['values'],
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $series['labels'],
        ];
    }
}
