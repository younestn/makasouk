<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentOrdersTable extends TableWidget
{
    protected int | string | array $columnSpan = [
        'default' => 1,
        'xl' => 6,
    ];

    protected function getTableHeading(): ?string
    {
        return 'Latest Orders';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->with(['customer', 'tailor', 'product'])
                    ->latest()
                    ->limit(10),
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tailor.name')
                    ->label('Tailor')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->label('Created'),
            ])
            ->recordUrl(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record]))
            ->emptyStateHeading('No orders yet')
            ->emptyStateDescription('New orders will appear here once customers start placing them.');
    }
}
