<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomOrderResource\Pages;
use App\Models\CustomOrder;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class CustomOrderResource extends Resource
{
    protected static ?string $model = CustomOrder::class;

    protected static ?string $navigationGroup = 'Commerce';

    protected static ?string $navigationIcon = 'heroicon-o-swatch';

    protected static ?string $navigationLabel = 'Custom Orders';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $count = CustomOrder::query()
            ->whereNotIn('status', [
                CustomOrder::STATUS_QUOTE_REJECTED,
                CustomOrder::STATUS_RECEIVED,
                CustomOrder::STATUS_DELIVERED,
                CustomOrder::STATUS_CANCELLED,
            ])
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Customer Request')
                ->columns(2)
                ->schema([
                    Forms\Components\Placeholder::make('customer')
                        ->label('Customer')
                        ->content(fn (?CustomOrder $record): string => $record?->customer?->name ?? '-'),
                    Forms\Components\Placeholder::make('created_at')
                        ->label('Created At')
                        ->content(fn (?CustomOrder $record): string => optional($record?->created_at)?->format('M d, Y H:i') ?? '-'),
                    Forms\Components\TextInput::make('title')
                        ->disabled(),
                    Forms\Components\TextInput::make('tailor_specialty')
                        ->label('Tailor Specialty')
                        ->disabled(),
                    Forms\Components\TextInput::make('fabric_type')
                        ->label('Fabric Type')
                        ->disabled(),
                    Forms\Components\TextInput::make('delivery_work_wilaya')
                        ->label('Delivery Wilaya')
                        ->disabled(),
                    Forms\Components\Placeholder::make('delivery_coordinates')
                        ->label('Delivery Coordinates')
                        ->content(fn (?CustomOrder $record): string => filled($record?->delivery_latitude) && filled($record?->delivery_longitude)
                            ? $record->delivery_latitude.', '.$record->delivery_longitude
                            : '-'),
                    Forms\Components\Placeholder::make('reference_images')
                        ->label('Reference Images')
                        ->content(function (?CustomOrder $record): HtmlString {
                            if (! $record) {
                                return new HtmlString('-');
                            }

                            $html = $record->images
                                ->map(fn ($image): string => sprintf(
                                    '<a href="%s" target="_blank" rel="noopener noreferrer" style="display:inline-block;margin:0 10px 10px 0;"><img src="%s" alt="Reference image" style="width:88px;height:88px;object-fit:cover;border-radius:12px;border:1px solid rgba(148,163,184,.25);"></a>',
                                    e($image->image_url),
                                    e($image->image_url),
                                ))
                                ->implode('');

                            return new HtmlString($html !== '' ? $html : '-');
                        })
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('notes')
                        ->rows(4)
                        ->disabled()
                        ->columnSpanFull(),
                    Forms\Components\KeyValue::make('measurements')
                        ->disabled()
                        ->columnSpanFull(),
                ]),
            Forms\Components\Section::make('Quote & Workflow')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('status')
                        ->options(collect(CustomOrder::allStatuses())->mapWithKeys(fn (string $status): array => [$status => str($status)->headline()->toString()])->all())
                        ->required(),
                    Forms\Components\Select::make('tailor_id')
                        ->label('Assigned Tailor')
                        ->options(fn (): array => User::query()
                            ->where('role', User::ROLE_TAILOR)
                            ->whereNotNull('approved_at')
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable()
                        ->preload(),
                    Forms\Components\TextInput::make('quote_amount')
                        ->label('Proposed Price')
                        ->numeric()
                        ->prefix('MAD')
                        ->minValue(0),
                    Forms\Components\Placeholder::make('quoted_at')
                        ->label('Quoted At')
                        ->content(fn (?CustomOrder $record): string => optional($record?->quoted_at)?->format('M d, Y H:i') ?? '-'),
                    Forms\Components\Textarea::make('quote_note')
                        ->label('Pricing Note')
                        ->rows(4)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('quote_rejection_note')
                        ->label('Customer Rejection Note')
                        ->rows(3)
                        ->disabled()
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['customer', 'tailor', 'images']))
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => str((string) $state)->headline()->toString())
                    ->color(fn (?string $state): string => match ($state) {
                        CustomOrder::STATUS_QUOTED, CustomOrder::STATUS_TAILOR_ASSIGNMENT_PENDING, CustomOrder::STATUS_PREPARING, CustomOrder::STATUS_SENT_TO_SHIPPING_CENTER => 'warning',
                        CustomOrder::STATUS_QUOTE_ACCEPTED, CustomOrder::STATUS_ASSIGNED_TO_TAILOR, CustomOrder::STATUS_WORK_STARTED, CustomOrder::STATUS_CUTTING_STARTED, CustomOrder::STATUS_SEWING_STARTED, CustomOrder::STATUS_ARRIVED => 'info',
                        CustomOrder::STATUS_RECEIVED, CustomOrder::STATUS_DELIVERED, CustomOrder::STATUS_COMPLETED => 'success',
                        CustomOrder::STATUS_QUOTE_REJECTED, CustomOrder::STATUS_CANCELLED => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('tailor_specialty')
                    ->label('Specialty')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tailor.name')
                    ->label('Assigned Tailor')
                    ->placeholder('-')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quote_amount')
                    ->label('Quote')
                    ->money('MAD')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('delivery_work_wilaya')
                    ->label('Delivery Wilaya')
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(CustomOrder::allStatuses())->mapWithKeys(fn (string $status): array => [$status => str($status)->headline()->toString()])->all()),
                Tables\Filters\SelectFilter::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('tailor_id')
                    ->relationship('tailor', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->paginated([10, 25, 50]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomOrders::route('/'),
            'edit' => Pages\EditCustomOrder::route('/{record}/edit'),
        ];
    }
}
