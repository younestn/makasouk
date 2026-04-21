<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Base\AdminPlaceholderPage;

class SettingsHub extends AdminPlaceholderPage
{
    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Platform Settings';

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Settings Hub';

    /**
     * @return array<int, array{title: string, description: string}>
     */
    public function getPlannedCapabilities(): array
    {
        return [
            [
                'title' => 'General platform preferences',
                'description' => 'Configure app identity, localization defaults, and contact details.',
            ],
            [
                'title' => 'Commerce policies',
                'description' => 'Prepare configurable rules for commissions, cancellation windows, and SLAs.',
            ],
            [
                'title' => 'Operational toggles',
                'description' => 'Enable or disable optional modules and maintenance-safe feature flags.',
            ],
        ];
    }
}
