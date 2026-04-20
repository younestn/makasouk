<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Category;
use App\Models\Order;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['customer', 'tailor', 'product.category']))
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('customer.name')->label('Customer')->searchable(),
                Tables\Columns\TextColumn::make('tailor.name')->label('Tailor')->searchable()->placeholder('-'),
                Tables\Columns\TextColumn::make('product.name')->label('Product')->searchable(),
                Tables\Columns\TextColumn::make('product.category.name')->label('Category')->toggleable(),
                Tables\Columns\TextColumn::make('measurements')
                    ->label('Measurements')
                    ->formatStateUsing(function ($state): string {
                        if (! is_array($state) || $state === []) {
                            return '-';
                        }

                        return collect($state)
                            ->map(fn ($value, $key): string => sprintf('%s: %s', $key, (string) $value))
                            ->implode(', ');
                    })
                    ->limit(80)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('accepted_at')->dateTime()->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('delivery_latitude')->label('Lat')->toggleable(),
                Tables\Columns\TextColumn::make('delivery_longitude')->label('Lng')->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(array_combine(Order::allStatuses(), Order::allStatuses())),
                Tables\Filters\SelectFilter::make('customer_id')->relationship('customer', 'name')->searchable()->preload(),
                Tables\Filters\SelectFilter::make('tailor_id')->relationship('tailor', 'name')->searchable()->preload(),
                Filter::make('category_id')
                    ->form([
                        Select::make('value')
                            ->label('Category')
                            ->options(Category::query()->orderBy('name')->pluck('name', 'id')->all()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas('product', fn (Builder $productQuery): Builder => $productQuery->where('category_id', $data['value']));
                    }),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date));
                    }),
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
