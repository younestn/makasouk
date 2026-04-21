<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Base\AdminPlaceholderPage;

class RolesPermissions extends AdminPlaceholderPage
{
    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Roles & Permissions';

    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Roles & Permissions';

    /**
     * @return array<int, array{title: string, description: string}>
     */
    public function getPlannedCapabilities(): array
    {
        return [
            [
                'title' => 'Granular admin roles',
                'description' => 'Define scoped permissions for support, operations, and content teams.',
            ],
            [
                'title' => 'Feature-level access policies',
                'description' => 'Map each navigation module to explicit permission gates.',
            ],
            [
                'title' => 'Audit-ready permission changes',
                'description' => 'Track who changed access rights and when those changes were applied.',
            ],
        ];
    }
}
