<?php

namespace App\Filament\Pages;

use App\Filament\Pages\ReportsCenter;
use App\Filament\Pages\SettingsHub;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\UserResource;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\HtmlString;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.dashboard');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.pages.dashboard');
    }

    public function getTitle(): string | HtmlString
    {
        return new HtmlString(__('admin.dashboard.title'));
    }

    public function getSubheading(): string | HtmlString | null
    {
        return __('admin.dashboard.subheading');
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('enterStore')
                ->label(__('admin.dashboard.enter_store'))
                ->icon('heroicon-o-building-storefront')
                ->url(url('/shop'))
                ->color('warning'),
            Action::make('addProduct')
                ->label(__('admin.dashboard.add_product'))
                ->icon('heroicon-o-plus')
                ->url(ProductResource::getUrl('create')),
            Action::make('addCategory')
                ->label(__('admin.dashboard.add_category'))
                ->icon('heroicon-o-tag')
                ->url(CategoryResource::getUrl('create')),
            Action::make('viewOrders')
                ->label(__('admin.dashboard.view_orders'))
                ->icon('heroicon-o-clipboard-document-list')
                ->url(OrderResource::getUrl('index')),
            Action::make('manageUsers')
                ->label(__('admin.dashboard.manage_users'))
                ->icon('heroicon-o-users')
                ->url(UserResource::getUrl('index')),
            Action::make('reports')
                ->label(__('admin.dashboard.reports'))
                ->icon('heroicon-o-chart-bar')
                ->url(ReportsCenter::getUrl()),
            Action::make('settings')
                ->label(__('admin.dashboard.settings'))
                ->icon('heroicon-o-cog-6-tooth')
                ->url(SettingsHub::getUrl()),
        ];
    }

    public function getColumns(): int | string | array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 12,
        ];
    }
}
