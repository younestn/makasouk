<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Base\AdminPlaceholderPage;

class CmsManager extends AdminPlaceholderPage
{
    protected static ?string $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'CMS / Pages / Banners';

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Content Management';

    /**
     * @return array<int, array{title: string, description: string}>
     */
    public function getPlannedCapabilities(): array
    {
        return [
            [
                'title' => 'Homepage and campaign banners',
                'description' => 'Manage hero banners, campaign cards, and seasonal promotions.',
            ],
            [
                'title' => 'Static page editor',
                'description' => 'Maintain FAQ, contact, policy, and onboarding page content.',
            ],
            [
                'title' => 'Content publishing workflow',
                'description' => 'Prepare draft, review, and publish lifecycle for content teams.',
            ],
        ];
    }
}
