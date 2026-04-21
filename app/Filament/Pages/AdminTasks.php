<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Base\AdminPlaceholderPage;

class AdminTasks extends AdminPlaceholderPage
{
    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Admin Tasks';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Admin Tasks Board';

    /**
     * @return array<int, array{title: string, description: string}>
     */
    public function getPlannedCapabilities(): array
    {
        return [
            [
                'title' => 'Pending admin reviews',
                'description' => 'Track pending approvals, moderation checks, and escalation items.',
            ],
            [
                'title' => 'Operational follow-ups',
                'description' => 'Plan daily follow-up tasks around delayed orders and support issues.',
            ],
            [
                'title' => 'Maintenance reminders',
                'description' => 'Keep release, data quality, and system health reminders in one place.',
            ],
        ];
    }
}
