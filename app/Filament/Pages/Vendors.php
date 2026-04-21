<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Base\AdminPlaceholderPage;

class Vendors extends AdminPlaceholderPage
{
    protected static ?string $navigationGroup = 'Commerce';

    protected static ?string $navigationLabel = 'Vendors & Sellers';

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Vendor Management';

    /**
     * @return array<int, array{title: string, description: string}>
     */
    public function getPlannedCapabilities(): array
    {
        return [
            [
                'title' => 'Vendor onboarding pipeline',
                'description' => 'Review business details, verification documents, and account readiness.',
            ],
            [
                'title' => 'Seller performance health',
                'description' => 'Track fulfillment quality, cancellation rates, and issue escalations.',
            ],
            [
                'title' => 'Vendor payout controls',
                'description' => 'Prepare payout scheduling and reconciliation workflows for future phases.',
            ],
        ];
    }
}
