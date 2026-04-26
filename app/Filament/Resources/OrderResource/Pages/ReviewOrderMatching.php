<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Services\Admin\OrderMatchingReviewService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ReviewOrderMatching extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected static string $view = 'filament.resources.order-resource.pages.review-order-matching';

    /**
     * @var array<string, mixed>
     */
    public array $reviewData = [];

    public function mount(int | string $record): void
    {
        parent::mount($record);

        $this->loadReviewData();
    }

    public function getTitle(): string | Htmlable
    {
        return 'Matching Review - Order #'.$this->getRecord()->id;
    }

    public function getSubheading(): ?string
    {
        return 'Review recommended tailor ranking, reasons, and operational context before assignment decisions.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('open_order')
                ->label('Open Order')
                ->icon('heroicon-o-eye')
                ->url(fn (): string => OrderResource::getUrl('view', ['record' => $this->getRecord()])),
            Actions\Action::make('open_recommended_tailor')
                ->label('Open Recommended Tailor')
                ->icon('heroicon-o-user-circle')
                ->color('info')
                ->visible(fn (): bool => filled(data_get($this->reviewData, 'recommended_tailor.resource_url')))
                ->url(fn (): ?string => data_get($this->reviewData, 'recommended_tailor.resource_url')),
            Actions\Action::make('recompute_matching')
                ->label('Refresh Matching')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->reviewData = app(OrderMatchingReviewService::class)->recompute($this->getRecord());

                    Notification::make()
                        ->title('Matching recommendation refreshed.')
                        ->success()
                        ->send();
                }),
        ];
    }

    private function loadReviewData(): void
    {
        $this->reviewData = app(OrderMatchingReviewService::class)->build($this->getRecord());
    }
}

