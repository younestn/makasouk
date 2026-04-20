<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationGroup = 'Operations';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('customer.name')->label('Customer')->searchable(),
                Tables\Columns\TextColumn::make('tailor.name')->label('Tailor')->searchable(),
                Tables\Columns\TextColumn::make('product.name')->label('Product')->searchable(),
                Tables\Columns\TextColumn::make('accepted_at')->dateTime(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('delivery_latitude')->label('Lat'),
                Tables\Columns\TextColumn::make('delivery_longitude')->label('Lng'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(array_combine(Order::allStatuses(), Order::allStatuses())),
                Tables\Filters\Filter::make('created_today')
                    ->query(fn ($query) => $query->whereDate('created_at', now()->toDateString())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
