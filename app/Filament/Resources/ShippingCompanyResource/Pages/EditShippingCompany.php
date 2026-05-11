<?php

namespace App\Filament\Resources\ShippingCompanyResource\Pages;

use App\Filament\Resources\ShippingCompanyResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditShippingCompany extends EditRecord
{
    protected static string $resource = ShippingCompanyResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['name'] = $data['name_en'] ?: ($data['name_ar'] ?? $data['name'] ?? null);
        $data['description'] = $data['description_en'] ?: ($data['description_ar'] ?? $data['description'] ?? null);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('admin.shipping_companies.notifications.saved'));
    }
}
