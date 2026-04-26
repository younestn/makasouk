<?php

namespace App\Filament\Widgets;

use App\Services\Admin\AdminDashboardStatsService;
use Filament\Widgets\Widget;

class AtelierVisualBlockWidget extends Widget
{
    protected static string $view = 'filament.widgets.atelier-visual-block-widget';

    protected static ?int $sort = 9;

    protected int | string | array $columnSpan = [
        'default' => 1,
        'xl' => 4,
    ];

    /**
     * @return array{topCategory: string, revenue: string}
     */
    protected function getViewData(): array
    {
        $statsService = app(AdminDashboardStatsService::class);
        $topCategories = $statsService->getMostRequestedCategories(1);
        $kpis = $statsService->getKpis();

        return [
            'topCategory' => $topCategories['labels'][0] ?? 'No category data yet',
            'revenue' => $kpis['total_revenue'] !== null
                ? number_format($kpis['total_revenue'], 2) . ' MAD'
                : 'N/A',
        ];
    }
}
