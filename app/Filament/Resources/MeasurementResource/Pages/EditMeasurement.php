<?php

namespace App\Filament\Resources\MeasurementResource\Pages;

use App\Filament\Resources\MeasurementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMeasurement extends EditRecord
{
    protected static string $resource = MeasurementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('admin.notifications.measurement_saved');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return MeasurementResource::normalizeFormData($data);
    }
}
