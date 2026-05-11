<?php

namespace App\Filament\Resources\CustomOrderResource\Pages;

use App\Filament\Resources\CustomOrderResource;
use App\Models\CustomOrder;
use App\Services\TrackingEventRecorder;
use App\Support\OrderTracking;
use Filament\Resources\Pages\EditRecord;

class EditCustomOrder extends EditRecord
{
    protected static string $resource = CustomOrderResource::class;

    /**
     * @var array<string, mixed>
     */
    protected array $originalState = [];

    public function mount(int | string $record): void
    {
        parent::mount($record);

        $this->originalState = $this->snapshot();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? null) === CustomOrder::STATUS_QUOTED && blank($this->getRecord()->quoted_at)) {
            $data['quoted_at'] = now();
        }

        if (($data['status'] ?? null) === CustomOrder::STATUS_ASSIGNED_TO_TAILOR && blank($this->getRecord()->assigned_at)) {
            $data['assigned_at'] = now();
        }

        if (filled($data['tailor_id'] ?? null) && blank($this->getRecord()->assigned_at)) {
            $data['assigned_at'] = now();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var CustomOrder $record */
        $record = $this->getRecord()->fresh(['trackingEvents']);
        $recorder = app(TrackingEventRecorder::class);

        if (
            ((string) ($this->originalState['status'] ?? '') !== (string) $record->status || blank($this->originalState['quoted_at']))
            && $record->status === CustomOrder::STATUS_QUOTED
            && ! $record->trackingEvents->contains(fn ($event): bool => $event->code === CustomOrder::STATUS_QUOTED)
        ) {
            $recorder->record($record, CustomOrder::STATUS_QUOTED, OrderTracking::ROLE_ADMIN, __('messages.custom_orders.timeline.quoted'));
        }

        if ((string) ($this->originalState['status'] ?? '') !== (string) $record->status && $record->status !== CustomOrder::STATUS_QUOTED) {
            $translationKey = 'messages.custom_orders.timeline.'.$record->status;
            $description = __($translationKey);

            $recorder->record(
                $record,
                (string) $record->status,
                OrderTracking::ROLE_ADMIN,
                $description !== $translationKey ? $description : null,
            );
        }

        if (
            (int) ($this->originalState['tailor_id'] ?? 0) !== (int) ($record->tailor_id ?? 0)
            && $record->tailor_id !== null
            && $record->status !== CustomOrder::STATUS_ASSIGNED_TO_TAILOR
            && ! $record->trackingEvents->contains(fn ($event): bool => $event->code === CustomOrder::STATUS_ASSIGNED_TO_TAILOR)
        ) {
            $recorder->record(
                $record,
                CustomOrder::STATUS_ASSIGNED_TO_TAILOR,
                OrderTracking::ROLE_ADMIN,
                __('messages.custom_orders.timeline.assigned_to_tailor'),
            );
        }

        $this->originalState = $this->snapshot();
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(): array
    {
        /** @var CustomOrder $record */
        $record = $this->getRecord();

        return [
            'status' => $record->status,
            'tailor_id' => $record->tailor_id,
            'quoted_at' => optional($record->quoted_at)?->toISOString(),
        ];
    }
}
