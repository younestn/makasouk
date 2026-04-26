<?php

namespace App\Filament\Resources\ContentPageResource\Pages;

use App\Filament\Resources\ContentPageResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateContentPage extends CreateRecord
{
    protected static string $resource = ContentPageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['slug'] ?? null) && filled($data['title_en'] ?? null)) {
            $data['slug'] = Str::slug((string) $data['title_en']);
        }

        return $data;
    }
}
