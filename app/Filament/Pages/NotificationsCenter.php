<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Base\AdminPlaceholderPage;

class NotificationsCenter extends AdminPlaceholderPage
{
    protected static ?string $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Notifications / Messages';

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Notifications Center';

    /**
     * @return array<int, array{title: string, description: string}>
     */
    public function getPlannedCapabilities(): array
    {
        return [
            [
                'title' => 'Broadcast announcements',
                'description' => 'Send platform-wide messages to customers, tailors, or both.',
            ],
            [
                'title' => 'Template management',
                'description' => 'Configure reusable notification templates for order lifecycle events.',
            ],
            [
                'title' => 'Delivery diagnostics',
                'description' => 'Inspect send logs, failures, and retry queues for communication channels.',
            ],
        ];
    }
}
