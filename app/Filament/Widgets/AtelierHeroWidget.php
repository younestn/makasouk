<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Services\Admin\AdminDashboardStatsService;
use Filament\Widgets\Widget;

class AtelierHeroWidget extends Widget
{
    protected static string $view = 'filament.widgets.atelier-hero-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;

    /**
     * @return array{
     *     heroStatItems: array<int, array{label: string, value: string, icon: string}>,
     *     storeUrl: string,
     *     ordersUrl: string
     * }
     */
    protected function getViewData(): array
    {
        $kpis = app(AdminDashboardStatsService::class)->getKpis();

        return [
            'heroStatItems' => [
                [
                    'label' => 'New Orders',
                    'value' => (string) $kpis['new_orders'],
                    'icon' => 'heroicon-o-sparkles',
                ],
                [
                    'label' => 'In Progress',
                    'value' => (string) $kpis['in_progress_orders'],
                    'icon' => 'heroicon-o-arrow-path',
                ],
                [
                    'label' => 'Completed',
                    'value' => (string) $kpis['completed_orders'],
                    'icon' => 'heroicon-o-check-badge',
                ],
            ],
            'storeUrl' => url('/shop'),
            'ordersUrl' => OrderResource::getUrl('index'),
        ];
    }
}
