<?php

namespace App\Filament\Widgets;

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
                    'label' => 'Add Product',
                    'description' => 'Create a new catalog product.',
                    'icon' => 'heroicon-o-plus-circle',
                    'url' => ProductResource::getUrl('create'),
                ],
                [
                    'label' => 'Add Category',
                    'description' => 'Add a new product category.',
                    'icon' => 'heroicon-o-tag',
                    'url' => CategoryResource::getUrl('create'),
                ],
                [
                    'label' => 'View Orders',
                    'description' => 'Review pending and active orders.',
                    'icon' => 'heroicon-o-clipboard-document-list',
                    'url' => OrderResource::getUrl('index'),
                ],
                [
                    'label' => 'Manage Users',
                    'description' => 'Suspend, unsuspend, and approve accounts.',
                    'icon' => 'heroicon-o-users',
                    'url' => UserResource::getUrl('index'),
                ],
                [
                    'label' => 'Open Reports',
                    'description' => 'Inspect operational KPIs and trends.',
                    'icon' => 'heroicon-o-chart-bar',
                    'url' => ReportsCenter::getUrl(),
                ],
                [
                    'label' => 'Open Settings',
                    'description' => 'Manage configuration placeholders.',
                    'icon' => 'heroicon-o-cog-6-tooth',
                    'url' => SettingsHub::getUrl(),
                ],
            ],
        ];
    }
}
