<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\PendingTailors;
use App\Filament\Pages\ReportsCenter;
use App\Filament\Pages\SettingsHub;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\UserResource;
use Filament\Widgets\Widget;

class QuickActions extends Widget
{
    protected static string $view = 'filament.widgets.quick-actions';

    protected int | string | array $columnSpan = 'full';

    /**
     * @return array{actions: array<int, array{label: string, description: string, icon: string, url: string}>}
     */
    protected function getViewData(): array
    {
        return [
            'actions' => [
                [
                    'label' => 'View Orders',
                    'description' => 'Track atelier pipeline and customer updates.',
                    'icon' => 'heroicon-o-clipboard-document-list',
                    'url' => OrderResource::getUrl('index'),
                ],
                [
                    'label' => 'Manage Services',
                    'description' => 'Update products and pricing catalogs.',
                    'icon' => 'heroicon-o-shopping-bag',
                    'url' => ProductResource::getUrl('index'),
                ],
                [
                    'label' => 'Manage Categories',
                    'description' => 'Curate service categories and hierarchy.',
                    'icon' => 'heroicon-o-tag',
                    'url' => CategoryResource::getUrl('index'),
                ],
                [
                    'label' => 'Manage Tailors',
                    'description' => 'Review approvals and provider readiness.',
                    'icon' => 'heroicon-o-user-group',
                    'url' => PendingTailors::getUrl(),
                ],
                [
                    'label' => 'Manage Customers',
                    'description' => 'Review user accounts and support actions.',
                    'icon' => 'heroicon-o-users',
                    'url' => UserResource::getUrl('index'),
                ],
                [
                    'label' => 'Open Store',
                    'description' => 'Preview live storefront merchandising.',
                    'icon' => 'heroicon-o-building-storefront',
                    'url' => url('/shop'),
                ],
                [
                    'label' => 'Reports',
                    'description' => 'Inspect KPIs and trend analytics.',
                    'icon' => 'heroicon-o-chart-bar',
                    'url' => ReportsCenter::getUrl(),
                ],
                [
                    'label' => 'Settings',
                    'description' => 'Manage system configuration hubs.',
                    'icon' => 'heroicon-o-cog-6-tooth',
                    'url' => SettingsHub::getUrl(),
                ],
            ],
        ];
    }
}
