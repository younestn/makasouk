<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Base\AdminPlaceholderPage;

class ReportsCenter extends AdminPlaceholderPage
{
    protected static ?string $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Reports & Analytics';

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Reports Center';

    /**
     * @return array<int, array{title: string, description: string}>
     */
    public function getPlannedCapabilities(): array
    {
        return [
            [
                'title' => 'Sales and revenue deep-dive',
                'description' => 'Break down completed orders by period, category, and tailor segment.',
            ],
            [
                'title' => 'Catalog performance insights',
                'description' => 'Understand demand by product, conversion bottlenecks, and top categories.',
            ],
            [
                'title' => 'User and vendor growth analytics',
                'description' => 'Track new user acquisition, vendor activation, and retention trends.',
            ],
        ];
    }
}
