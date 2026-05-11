<?php

namespace App\Filament\Resources\FabricResource\Pages;

use App\Filament\Resources\FabricResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFabric extends CreateRecord
{
    protected static string $resource = FabricResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('admin.notifications.fabric_created');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return FabricResource::normalizeFormData($data);
    }
}
