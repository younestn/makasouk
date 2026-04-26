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
    protected static ?string $navigationGroup = 'Dashboard';

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Control Center';

    public function getTitle(): string | HtmlString
    {
        return new HtmlString('Makasouk Atelier Control Center');
    }

    public function getSubheading(): string | HtmlString | null
    {
        return 'Oversee bespoke tailoring operations, monitor order craftsmanship flow, and act quickly on critical tasks.';
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('enterStore')
                ->label('Enter Store')
                ->icon('heroicon-o-building-storefront')
                ->url(url('/shop'))
                ->color('warning'),
            Action::make('addProduct')
                ->label('Add Product')
                ->icon('heroicon-o-plus')
                ->url(ProductResource::getUrl('create')),
            Action::make('addCategory')
                ->label('Add Category')
                ->icon('heroicon-o-tag')
                ->url(CategoryResource::getUrl('create')),
            Action::make('viewOrders')
                ->label('View Orders')
                ->icon('heroicon-o-clipboard-document-list')
                ->url(OrderResource::getUrl('index')),
            Action::make('manageUsers')
                ->label('Manage Users')
                ->icon('heroicon-o-users')
                ->url(UserResource::getUrl('index')),
            Action::make('reports')
                ->label('Reports')
                ->icon('heroicon-o-chart-bar')
                ->url(ReportsCenter::getUrl()),
            Action::make('settings')
                ->label('Settings')
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
