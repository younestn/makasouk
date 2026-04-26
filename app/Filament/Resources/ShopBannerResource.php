<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShopBannerResource\Pages;
use App\Models\ShopBanner;
use App\Support\Filament\AdminUiState;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class ShopBannerResource extends Resource
{
    protected static ?string $model = ShopBanner::class;

    protected static ?string $navigationGroup = 'Content';

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Shop Banners';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.resources.shop_banners');
    }

    public static function getModelLabel(): string
    {
        return __('admin.models.shop_banner');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.models.shop_banners');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Banner Content')
                ->description('Primary promotional content shown in the shop hero carousel.')
                ->icon('heroicon-o-photo')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('New Seasonal Collection'),
                    Forms\Components\TextInput::make('badge')
                        ->maxLength(60)
                        ->placeholder('Limited'),
                    Forms\Components\TextInput::make('subtitle')
                        ->columnSpanFull()
                        ->maxLength(255)
                        ->placeholder('Crafted looks for your next special event'),
                    Forms\Components\FileUpload::make('image_path')
                        ->label('Banner Image')
                        ->image()
                        ->disk('public')
                        ->directory('shop/banners')
                        ->visibility('public')
                        ->required()
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('button_text')
                        ->maxLength(80)
                        ->placeholder('Shop now'),
                    Forms\Components\TextInput::make('button_link')
                        ->url()
                        ->maxLength(255)
                        ->placeholder('https://makasouk.local/shop'),
                ]),
            Section::make('Publishing')
                ->description('Control where and when this banner is visible.')
                ->icon('heroicon-o-calendar-days')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('placement')
                        ->default('shop_hero')
                        ->required()
                        ->maxLength(50)
                        ->helperText('Use "home_hero" for the public homepage hero, or "shop_hero" for the shop slider.'),
                    Forms\Components\TextInput::make('display_order')
                        ->integer()
                        ->default(0)
                        ->required(),
                    Forms\Components\Toggle::make('is_active')->default(true)->inline(false),
                    Forms\Components\DateTimePicker::make('publish_starts_at'),
                    Forms\Components\DateTimePicker::make('publish_ends_at')
                        ->after('publish_starts_at'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->disk('public')
                    ->label('Preview')
                    ->square(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->description(fn (ShopBanner $record): ?string => $record->subtitle),
                Tables\Columns\TextColumn::make('placement')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => str($state)->headline()->replace('_', ' ')->toString())
                    ->color('primary'),
                Tables\Columns\TextColumn::make('visibility_state')
                    ->label('Visibility')
                    ->state(function (ShopBanner $record): string {
                        if (! $record->is_active) {
                            return 'Inactive';
                        }

                        if ($record->publish_starts_at && $record->publish_starts_at->isFuture()) {
                            return 'Scheduled';
                        }

                        if ($record->publish_ends_at && $record->publish_ends_at->isPast()) {
                            return 'Expired';
                        }

                        return 'Live';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Live' => 'success',
                        'Scheduled' => 'warning',
                        'Expired' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('display_order')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => AdminUiState::toggleStatusLabel($state))
                    ->color(fn (bool $state): string => AdminUiState::toggleStatusColor($state)),
                Tables\Columns\TextColumn::make('publish_starts_at')
                    ->label('Starts')
                    ->dateTime('M d, Y H:i')
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('publish_ends_at')
                    ->label('Ends')
                    ->dateTime('M d, Y H:i')
                    ->placeholder('-')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
                Tables\Filters\SelectFilter::make('placement')
                    ->options([
                        'home_hero' => 'Homepage Hero',
                        'shop_hero' => 'Shop Hero',
                    ]),
                Filter::make('currently_published')
                    ->label('Currently Published')
                    ->query(fn ($query) => $query->currentlyPublished()),
            ])
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil-square'),
                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('display_order')
            ->paginated([10, 25, 50]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShopBanners::route('/'),
            'create' => Pages\CreateShopBanner::route('/create'),
            'edit' => Pages\EditShopBanner::route('/{record}/edit'),
        ];
    }
}
