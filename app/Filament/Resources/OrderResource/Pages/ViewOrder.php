<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('matching_review')
                ->label('Matching Review')
                ->icon('heroicon-o-sparkles')
                ->color('info')
                ->url(fn (): string => OrderResource::getUrl('matching-review', ['record' => $this->getRecord()])),
        ];
    }
}
