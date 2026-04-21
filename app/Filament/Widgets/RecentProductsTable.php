<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentProductsTable extends TableWidget
{
    protected int | string | array $columnSpan = [
        'default' => 1,
        'xl' => 3,
    ];

    protected function getTableHeading(): ?string
    {
        return 'Recent Products';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->with('category')
                    ->latest()
                    ->limit(8),
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->limit(24),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('price')
                    ->money('MAD'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->recordUrl(fn (Product $record): string => ProductResource::getUrl('view', ['record' => $record]))
            ->emptyStateHeading('No products yet')
            ->emptyStateDescription('Newly created products will appear in this table.');
    }
}
