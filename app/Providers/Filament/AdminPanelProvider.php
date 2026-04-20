<?php

namespace App\Providers\Filament;

use App\Filament\Pages\AdminTasks;
use App\Filament\Pages\CmsManager;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\NotificationsCenter;
use App\Filament\Pages\PendingTailors;
use App\Filament\Pages\ReportsCenter;
use App\Filament\Pages\RolesPermissions;
use App\Filament\Pages\SettingsHub;
use App\Filament\Pages\Vendors;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ReviewResource;
use App\Filament\Resources\UserResource;
use App\Filament\Widgets\AdminStatsOverview;
use App\Filament\Widgets\OrdersTrendChart;
use App\Filament\Widgets\QuickActions;
use App\Filament\Widgets\RecentOrdersTable;
use App\Filament\Widgets\RecentProductsTable;
use App\Filament\Widgets\RecentUsersTable;
use App\Filament\Widgets\RevenueTrendChart;
use App\Filament\Widgets\UserRegistrationsTrendChart;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin-panel')
            ->brandName('Makasouk Admin')
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('18rem')
            ->collapsedSidebarWidth('5rem')
            ->collapsibleNavigationGroups()
            ->login()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->navigationGroups([
                NavigationGroup::make('Dashboard')
                    ->icon('heroicon-o-home')
                    ->collapsible(false),
                NavigationGroup::make('Commerce')
                    ->icon('heroicon-o-shopping-cart'),
                NavigationGroup::make('Catalog')
                    ->icon('heroicon-o-squares-2x2'),
                NavigationGroup::make('Users')
                    ->icon('heroicon-o-users'),
                NavigationGroup::make('Content')
                    ->icon('heroicon-o-document-text'),
                NavigationGroup::make('Reports')
                    ->icon('heroicon-o-chart-bar'),
                NavigationGroup::make('Administration')
                    ->icon('heroicon-o-shield-check'),
                NavigationGroup::make('Settings')
                    ->icon('heroicon-o-cog-6-tooth'),
            ])
            ->pages([
                Dashboard::class,
                PendingTailors::class,
                Vendors::class,
                ReportsCenter::class,
                AdminTasks::class,
                CmsManager::class,
                NotificationsCenter::class,
                RolesPermissions::class,
                SettingsHub::class,
            ])
            ->resources([
                UserResource::class,
                CategoryResource::class,
                ProductResource::class,
                OrderResource::class,
                ReviewResource::class,
            ])
            ->widgets([
                AdminStatsOverview::class,
                QuickActions::class,
                OrdersTrendChart::class,
                UserRegistrationsTrendChart::class,
                RevenueTrendChart::class,
                RecentOrdersTable::class,
                RecentUsersTable::class,
                RecentProductsTable::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
