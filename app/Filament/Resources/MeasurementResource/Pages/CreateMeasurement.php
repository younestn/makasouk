<?php

namespace App\Filament\Resources\MeasurementResource\Pages;

use App\Filament\Resources\MeasurementResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMeasurement extends CreateRecord
{
    protected static string $resource = MeasurementResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('admin.notifications.measurement_created');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return MeasurementResource::normalizeFormData($data);
    }
}
