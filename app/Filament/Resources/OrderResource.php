<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Category;
use App\Models\Order;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use App\Support\Filament\AdminUiState;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationGroup = 'Commerce';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Orders';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $openOrders = Order::query()
            ->whereIn('status', [
                Order::STATUS_PENDING,
                Order::STATUS_SEARCHING_FOR_TAILOR,
                Order::STATUS_NO_TAILORS_AVAILABLE,
                Order::STATUS_ACCEPTED,
                Order::STATUS_PROCESSING,
                Order::STATUS_READY_FOR_DELIVERY,
            ])
            ->count();

        return $openOrders > 0 ? (string) $openOrders : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['customer', 'tailor', 'product.category', 'product.fabric']))
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => AdminUiState::orderStatusLabel($state))
                    ->color(fn (?string $state): string => AdminUiState::orderStatusColor($state))
                    ->icon(fn (?string $state): string => AdminUiState::orderStatusIcon($state)),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tailor.name')
                    ->label('Tailor')
                    ->searchable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable(),
                Tables\Columns\TextColumn::make('product.display_fabric_type')
                    ->label('Fabric Type')
                    ->state(fn (Order $record): ?string => $record->product?->display_fabric_type)
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('product.display_fabric_country')
                    ->label('Fabric Country')
                    ->state(fn (Order $record): ?string => $record->product?->display_fabric_country)
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('product.category.name')
                    ->label('Category')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('matched_specialization')
                    ->label('Matched Specialization')
                    ->badge()
                    ->color('primary')
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('product.price')
                    ->label('Amount')
                    ->money('MAD')
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('measurements')
                    ->label('Measurements')
                    ->formatStateUsing(function ($state): string {
                        if (! is_array($state) || $state === []) {
                            return '-';
                        }

                        return collect($state)
                            ->map(fn ($value, $key): string => sprintf('%s: %s', str((string) $key)->headline(), (string) $value))
                            ->implode(', ');
                    })
                    ->limit(80)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('accepted_at')
                    ->label('Accepted')
                    ->since()
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('delivery_work_wilaya')
                    ->label('Delivery Wilaya')
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('delivery_location_label')
                    ->label('Delivery Label')
                    ->placeholder('-')
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('matching_snapshot.recommended_tailor_id')
                    ->label('Recommended Tailor')
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('delivery_coordinates')
                    ->label('Delivery Coordinates')
                    ->state(fn (Order $record): string => filled($record->delivery_latitude) && filled($record->delivery_longitude)
                        ? $record->delivery_latitude.', '.$record->delivery_longitude
                        : '-')
                    ->placeholder('-')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(Order::allStatuses())
                        ->mapWithKeys(fn (string $status): array => [$status => AdminUiState::orderStatusLabel($status)])
                        ->all()),
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
                Filter::make('matched_specialization')
                    ->form([
                        Select::make('value')
                            ->label('Matched Specialization')
                            ->options(Category::query()->orderBy('tailor_specialization')->pluck('tailor_specialization', 'tailor_specialization')->filter()->all()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->where('matched_specialization', $data['value']);
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
            ->filtersFormColumns(4)
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye'),
                Tables\Actions\Action::make('matching_review')
                    ->label('Matching Review')
                    ->icon('heroicon-o-sparkles')
                    ->color('info')
                    ->url(fn (Order $record): string => static::getUrl('matching-review', ['record' => $record])),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Order Summary')
                    ->columns(3)
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('Order #'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => AdminUiState::orderStatusLabel($state))
                            ->color(fn (?string $state): string => AdminUiState::orderStatusColor($state))
                            ->icon(fn (?string $state): string => AdminUiState::orderStatusIcon($state)),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime('M d, Y H:i'),
                        Infolists\Components\TextEntry::make('customer.name')
                            ->label('Customer')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('tailor.name')
                            ->label('Tailor')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('matched_specialization')
                            ->label('Matched Specialization')
                            ->badge()
                            ->placeholder('-'),
                    ]),
                Infolists\Components\Section::make('Product & Fabric')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('product.name')
                            ->label('Product')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('product.category.name')
                            ->label('Category')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('product.display_fabric_type')
                            ->label('Fabric Type')
                            ->state(fn (Order $record): ?string => $record->product?->display_fabric_type)
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('product.display_fabric_country')
                            ->label('Fabric Country')
                            ->state(fn (Order $record): ?string => $record->product?->display_fabric_country)
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('product.display_fabric_description')
                            ->label('Fabric Description')
                            ->state(fn (Order $record): ?string => $record->product?->display_fabric_description)
                            ->placeholder('-')
                            ->columnSpanFull(),
                        Infolists\Components\ImageEntry::make('fabric_image_url')
                            ->label('Fabric Image')
                            ->state(fn (Order $record): ?string => $record->product?->fabric_image_url)
                            ->height(140)
                            ->extraImgAttributes(['class' => 'rounded-lg object-cover'])
                            ->columnSpanFull(),
                    ]),
                Infolists\Components\Section::make('Delivery')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('delivery_work_wilaya')
                            ->label('Delivery Wilaya')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('delivery_location_label')
                            ->label('Delivery Label')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('delivery_latitude')
                            ->label('Latitude')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('delivery_longitude')
                            ->label('Longitude')
                            ->placeholder('-'),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'matching-review' => Pages\ReviewOrderMatching::route('/{record}/matching-review'),
        ];
    }
}
