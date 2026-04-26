<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\AdminLogin;
use App\Filament\Pages\AdminTasks;
use App\Filament\Pages\CmsManager;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\MailSettings;
use App\Filament\Pages\MapProviderSettings;
use App\Filament\Pages\NotificationsCenter;
use App\Filament\Pages\PendingTailors;
use App\Filament\Pages\ReportsCenter;
use App\Filament\Pages\RolesPermissions;
use App\Filament\Pages\SettingsHub;
use App\Filament\Pages\ShopSettings;
use App\Filament\Pages\SmsProviderSettings;
use App\Filament\Pages\Vendors;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\ContentPageResource;
use App\Filament\Resources\FabricResource;
use App\Filament\Resources\MeasurementResource;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ReviewResource;
use App\Filament\Resources\ShopBannerResource;
use App\Filament\Resources\UserResource;
use App\Filament\Widgets\AdminStatsOverview;
use App\Filament\Widgets\AtelierHeroWidget;
use App\Filament\Widgets\AtelierVisualBlockWidget;
use App\Filament\Widgets\MostRequestedServicesChart;
use App\Filament\Widgets\OrdersTrendChart;
use App\Filament\Widgets\QuickActions;
use App\Filament\Widgets\RecentOrdersTable;
use App\Filament\Widgets\RecentProductsTable;
use App\Filament\Widgets\RecentUsersTable;
use App\Filament\Widgets\RevenueTrendChart;
use App\Filament\Widgets\UserRegistrationsTrendChart;
use App\Http\Middleware\SetApplicationLocale;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\View\PanelsRenderHook;
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
            ->brandName(__('admin.brand.name'))
            ->brandLogo(asset('favicon.ico'))
            ->sidebarWidth('17rem')
            ->sidebarCollapsibleOnDesktop()
            ->sidebarFullyCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->login(AdminLogin::class)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->navigationGroups([
                NavigationGroup::make(__('admin.navigation.groups.dashboard'))
                    ->collapsible(false),
                NavigationGroup::make(__('admin.navigation.groups.commerce'))
                    ->collapsible(),
                NavigationGroup::make(__('admin.navigation.groups.catalog'))
                    ->collapsible(),
                NavigationGroup::make(__('admin.navigation.groups.users'))
                    ->collapsible(),
                NavigationGroup::make(__('admin.navigation.groups.content'))
                    ->collapsible(),
                NavigationGroup::make(__('admin.navigation.groups.reports'))
                    ->collapsible(),
                NavigationGroup::make(__('admin.navigation.groups.administration'))
                    ->collapsible(),
                NavigationGroup::make(__('admin.navigation.groups.settings'))
                    ->collapsible(),
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
                ShopSettings::class,
                MailSettings::class,
                SmsProviderSettings::class,
                MapProviderSettings::class,
            ])
            ->resources([
                UserResource::class,
                CategoryResource::class,
                FabricResource::class,
                MeasurementResource::class,
                ProductResource::class,
                OrderResource::class,
                ReviewResource::class,
                ContentPageResource::class,
                ShopBannerResource::class,
            ])
            ->widgets([
                AtelierHeroWidget::class,
                AdminStatsOverview::class,
                QuickActions::class,
                OrdersTrendChart::class,
                UserRegistrationsTrendChart::class,
                RevenueTrendChart::class,
                MostRequestedServicesChart::class,
                AtelierVisualBlockWidget::class,
                RecentOrdersTable::class,
                RecentUsersTable::class,
                RecentProductsTable::class,
            ])
            ->renderHook(
                PanelsRenderHook::TOPBAR_END,
                fn (): \Illuminate\Contracts\View\View => view('filament.partials.locale-switcher'),
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                SetApplicationLocale::class,
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
