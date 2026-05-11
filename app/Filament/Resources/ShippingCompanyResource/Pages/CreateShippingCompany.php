<?php

namespace App\Filament\Resources\ShippingCompanyResource\Pages;

use App\Filament\Resources\ShippingCompanyResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateShippingCompany extends CreateRecord
{
    protected static string $resource = ShippingCompanyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['name'] = $data['name_en'] ?: ($data['name_ar'] ?? $data['name'] ?? null);
        $data['description'] = $data['description_en'] ?: ($data['description_ar'] ?? $data['description'] ?? null);

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('admin.shipping_companies.notifications.created'));
    }
}
