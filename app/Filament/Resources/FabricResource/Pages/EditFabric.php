<?php

namespace App\Filament\Resources\FabricResource\Pages;

use App\Filament\Resources\FabricResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFabric extends EditRecord
{
    protected static string $resource = FabricResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('admin.notifications.fabric_saved');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return FabricResource::normalizeFormData($data);
    }
}
