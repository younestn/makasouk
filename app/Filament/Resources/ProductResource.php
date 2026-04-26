<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Fabric;
use App\Models\Measurement;
use App\Models\Product;
use App\Support\Filament\AdminUiState;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.catalog');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.resources.products');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('Product')
                ->persistTabInQueryString()
                ->tabs([
                    Tabs\Tab::make('General')
                        ->schema([
                            Section::make('Core Information')
                                ->description('Define product identity and customer-facing details.')
                                ->columns(2)
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->required()
                                        ->maxLength(255)
                                        ->placeholder('Premium Caftan'),
                                    Forms\Components\TextInput::make('slug')
                                        ->required()
                                        ->maxLength(255)
                                        ->unique(ignoreRecord: true)
                                        ->placeholder('premium-caftan'),
                                    Forms\Components\Select::make('category_id')
                                        ->relationship('category', 'name')
                                        ->required()
                                        ->searchable()
                                        ->preload(),
                                    Forms\Components\TextInput::make('sku')
                                        ->maxLength(255)
                                        ->placeholder('MK-PRD-001'),
                                    Forms\Components\Textarea::make('short_description')
                                        ->columnSpanFull()
                                        ->rows(3)
                                        ->placeholder('A concise summary displayed in cards and listings.')
                                        ->maxLength(65535),
                                    Forms\Components\Textarea::make('description')
                                        ->columnSpanFull()
                                        ->rows(5)
                                        ->placeholder('Full service details, craftsmanship notes, and customer expectations.')
                                        ->maxLength(65535),
                                ]),
                        ]),
                    Tabs\Tab::make('Pricing & Stock')
                        ->schema([
                            Section::make('Commerce Controls')
                                ->description('Keep pricing and inventory aligned with atelier operations.')
                                ->columns(2)
                                ->schema([
                                    Forms\Components\Select::make('pricing_type')
                                        ->options([
                                            'fixed' => 'Fixed',
                                            'estimated' => 'Estimated',
                                        ])
                                        ->required(),
                                    Forms\Components\TextInput::make('price')
                                        ->numeric()
                                        ->required()
                                        ->prefix('MAD'),
                                    Forms\Components\TextInput::make('sale_price')
                                        ->numeric()
                                        ->prefix('MAD')
                                        ->helperText('Optional campaign price used when active.'),
                                    Forms\Components\TextInput::make('stock')
                                        ->integer()
                                        ->minValue(0)
                                        ->default(0)
                                        ->required(),
                                ]),
                        ]),
                    Tabs\Tab::make('Storefront')
                        ->schema([
                            Section::make('Merchandising')
                                ->description('Control publication, spotlight flags, and storefront ordering.')
                                ->columns(2)
                                ->schema([
                                    Forms\Components\FileUpload::make('main_image')
                                        ->image()
                                        ->disk('public')
                                        ->directory('shop/products')
                                        ->visibility('public'),
                                    Forms\Components\FileUpload::make('pattern_file_path')
                                        ->label(__('admin.products.fields.pattern_file'))
                                        ->disk('public')
                                        ->directory('shop/products/patterns')
                                        ->visibility('public')
                                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                                        ->helperText(__('admin.products.help.pattern_file')),
                                    Forms\Components\DateTimePicker::make('published_at'),
                                    Forms\Components\TextInput::make('sort_order')
                                        ->integer()
                                        ->default(0)
                                        ->required()
                                        ->helperText('Lower numbers surface first in curated sections.'),
                                    Forms\Components\Hidden::make('created_by_admin_id')
                                        ->default(fn (): ?int => auth()->id())
                                        ->dehydrated(fn (?Product $record): bool => $record === null),
                                    Forms\Components\Toggle::make('is_active')->default(true)->inline(false),
                                    Forms\Components\Toggle::make('is_featured')->default(false)->inline(false),
                                    Forms\Components\Toggle::make('is_best_seller')->default(false)->inline(false),
                                ]),
                        ]),
                    Tabs\Tab::make('Fabric')
                        ->schema([
                            Section::make('Fabric Library')
                                ->description('Assign a reusable fabric entry from the central library. This becomes the source of truth for customer, tailor, and admin order views.')
                                ->columns(2)
                                ->schema([
                                    Forms\Components\Select::make('fabric_id')
                                        ->label('Library Fabric')
                                        ->relationship(
                                            name: 'fabric',
                                            titleAttribute: 'name',
                                            modifyQueryUsing: fn (Builder $query): Builder => $query
                                                ->where('is_active', true)
                                                ->orderBy('sort_order')
                                                ->orderBy('name'),
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->nullable()
                                        ->getOptionLabelFromRecordUsing(fn (Fabric $record): string => trim(sprintf(
                                            '%s%s',
                                            $record->name,
                                            filled($record->country) ? ' ('.$record->country.')' : '',
                                        )))
                                        ->helperText('If selected, this library fabric is shown everywhere. Legacy fields below remain as a safe fallback.'),
                                    Forms\Components\Placeholder::make('fabric_library_preview')
                                        ->label('Display behavior')
                                        ->content('Products use library fabric details first, then fall back to legacy product-level fabric fields if no library fabric is assigned.'),
                                ]),
                            Section::make('Legacy Fabric Fallback')
                                ->description('Optional product-level fabric details kept for older products and fallback display.')
                                ->collapsible()
                                ->collapsed()
                                ->columns(2)
                                ->schema([
                                    Forms\Components\TextInput::make('fabric_type')
                                        ->maxLength(120)
                                        ->placeholder('Cotton Twill'),
                                    Forms\Components\TextInput::make('fabric_country')
                                        ->maxLength(120)
                                        ->placeholder('Turkey'),
                                    Forms\Components\FileUpload::make('fabric_image_path')
                                        ->label('Fabric Image')
                                        ->image()
                                        ->disk('public')
                                        ->directory('shop/products/fabrics')
                                        ->visibility('public')
                                        ->helperText('Optional visual reference shown to customers and tailors.'),
                                    Forms\Components\Textarea::make('fabric_description')
                                        ->columnSpanFull()
                                        ->rows(4)
                                        ->maxLength(1000)
                                        ->placeholder('Texture notes, composition, handling instructions, or care hints.'),
                                ]),
                        ]),
                    Tabs\Tab::make('Measurements')
                        ->schema([
                            Section::make('Required Client Measurements')
                                ->description('Choose which measurement fields customers must fill when ordering this product.')
                                ->columns(1)
                                ->schema([
                                    Forms\Components\Select::make('measurements')
                                        ->label('Measurement Fields')
                                        ->relationship(
                                            name: 'measurements',
                                            titleAttribute: 'name',
                                            modifyQueryUsing: fn (Builder $query): Builder => $query
                                                ->where('is_active', true)
                                                ->orderBy('sort_order')
                                                ->orderBy('name'),
                                        )
                                        ->multiple()
                                        ->searchable()
                                        ->preload()
                                        ->helperText('Only active measurement definitions are selectable. Customer order forms will render exactly these fields.')
                                        ->getOptionLabelFromRecordUsing(fn (Measurement $record): string => sprintf(
                                            '%s (%s)',
                                            $record->name,
                                            str($record->audience)->headline()->toString(),
                                        )),
                                ]),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['category', 'createdByAdmin', 'fabric'])->withCount('measurements'))
            ->columns([
                Tables\Columns\ImageColumn::make('main_image')
                    ->disk('public')
                    ->label('Image')
                    ->square(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Product $record): ?string => $record->category?->name),
                Tables\Columns\TextColumn::make('display_fabric_type')
                    ->label('Fabric')
                    ->state(fn (Product $record): ?string => $record->display_fabric_type)
                    ->description(fn (Product $record): ?string => $record->fabric_id ? 'Library' : (filled($record->display_fabric_type) ? 'Legacy fallback' : null))
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('display_fabric_country')
                    ->label('Fabric Country')
                    ->state(fn (Product $record): ?string => $record->display_fabric_country)
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('pricing_type')
                    ->label('Pricing')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => str($state)->headline()->toString())
                    ->color(fn (string $state): string => $state === 'fixed' ? 'primary' : 'warning'),
                Tables\Columns\TextColumn::make('price')
                    ->money('MAD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale_price')
                    ->money('MAD')
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('stock')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('stock_state')
                    ->label('Stock Status')
                    ->state(fn (Product $record): int => (int) $record->stock)
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => AdminUiState::stockLevelLabel($state))
                    ->color(fn (int $state): string => AdminUiState::stockLevelColor($state)),
                Tables\Columns\TextColumn::make('sku')
                    ->toggleable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => AdminUiState::toggleStatusLabel($state))
                    ->color(fn (bool $state): string => AdminUiState::toggleStatusColor($state))
                    ->icon(fn (bool $state): string => $state ? 'heroicon-m-check-circle' : 'heroicon-m-pause-circle'),
                Tables\Columns\TextColumn::make('is_featured')
                    ->label('Featured')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Featured' : 'Standard')
                    ->color(fn (bool $state): string => $state ? 'warning' : 'gray')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('is_best_seller')
                    ->label('Best Seller')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Best Seller' : 'Regular')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('createdByAdmin.name')
                    ->label('Creator')
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('measurements_count')
                    ->label('Measurements')
                    ->badge()
                    ->color('info')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('pricing_type')
                    ->label('Pricing')
                    ->options([
                        'fixed' => 'Fixed',
                        'estimated' => 'Estimated',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
                Tables\Filters\TernaryFilter::make('is_featured')->label('Featured'),
                Tables\Filters\TernaryFilter::make('is_best_seller')->label('Best Seller'),
                Filter::make('low_stock')
                    ->label('Low Stock (<= 5)')
                    ->query(fn (Builder $query): Builder => $query->where('stock', '<=', 5)),
            ])
            ->filtersFormColumns(4)
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye'),
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil-square'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc')
            ->paginated([10, 25, 50]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
