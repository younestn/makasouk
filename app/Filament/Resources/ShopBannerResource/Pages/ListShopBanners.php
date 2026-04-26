<?php

namespace App\Filament\Resources\ShopBannerResource\Pages;

use App\Filament\Resources\ShopBannerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShopBanners extends ListRecords
{
    protected static string $resource = ShopBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
