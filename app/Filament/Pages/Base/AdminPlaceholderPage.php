<?php

namespace App\Filament\Pages\Base;

use Filament\Pages\Page;

abstract class AdminPlaceholderPage extends Page
{
    protected static string $view = 'filament.pages.admin-placeholder';

    /**
     * @return array<int, array{title: string, description: string}>
     */
    abstract public function getPlannedCapabilities(): array;

    public function getPlaceholderTitle(): string
    {
        return (string) static::$title;
    }

    public function getPlaceholderDescription(): ?string
    {
        return static::$navigationLabel;
    }
}
