<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Fabric;
use App\Models\Measurement;
use App\Models\Product;
use App\Support\Filament\AdminUiState;
use App\Support\Tailor\MeasurementOptions;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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

    public static function getModelLabel(): string
    {
        return __('admin.models.product');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.models.products');
    }

    public static function normalizeFormData(array $data): array
    {
        $galleryImages = collect($data['gallery_images'] ?? [])
            ->filter(fn ($path): bool => filled($path))
            ->values()
            ->all();

        $patternFiles = collect($data['pattern_files'] ?? [])
            ->filter(fn ($path): bool => filled($path))
            ->values()
            ->all();

        $specifications = self::normalizeSpecificationRows($data['specifications'] ?? []);
        $colorOptions = self::normalizeColorRows($data['color_options'] ?? []);

        $data['gallery_images'] = $galleryImages === [] ? null : $galleryImages;
        $data['pattern_files'] = $patternFiles === [] ? null : $patternFiles;
        $data['specifications'] = $specifications === [] ? null : $specifications;
        $data['color_options'] = $colorOptions === [] ? null : $colorOptions;

        if (blank($data['main_image'] ?? null) && $galleryImages !== []) {
            $data['main_image'] = $galleryImages[0];
        }

        if (blank($data['pattern_file_path'] ?? null) && $patternFiles !== []) {
            $data['pattern_file_path'] = $patternFiles[0];
        }

        if (blank($data['slug'] ?? null) && filled($data['name'] ?? null)) {
            $slug = Str::slug((string) $data['name']);

            if (filled($slug)) {
                $data['slug'] = $slug;
            }
        }

        return $data;
    }

    /**
     * @param  array<int, mixed>  $rows
     * @return array<int, array{key: string, label_en: string|null, label_ar: string|null, value_en: string|null, value_ar: string|null}>
     */
    private static function normalizeSpecificationRows(array $rows): array
    {
        return collect($rows)
            ->filter(fn ($row): bool => is_array($row))
            ->map(function (array $row): ?array {
                $labelEn = filled($row['label_en'] ?? null) ? trim((string) $row['label_en']) : null;
                $labelAr = filled($row['label_ar'] ?? null) ? trim((string) $row['label_ar']) : null;
                $valueEn = filled($row['value_en'] ?? null) ? trim((string) $row['value_en']) : null;
                $valueAr = filled($row['value_ar'] ?? null) ? trim((string) $row['value_ar']) : null;

                if (($labelEn === null && $labelAr === null) || ($valueEn === null && $valueAr === null)) {
                    return null;
                }

                $key = filled($row['key'] ?? null)
                    ? Str::slug((string) $row['key'])
                    : Str::slug((string) ($labelEn ?: $labelAr ?: Str::random(8)));

                return [
                    'key' => $key !== '' ? $key : Str::random(8),
                    'label_en' => $labelEn,
                    'label_ar' => $labelAr,
                    'value_en' => $valueEn,
                    'value_ar' => $valueAr,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, mixed>  $rows
     * @return array<int, array{key: string, name_en: string|null, name_ar: string|null, hex: string|null}>
     */
    private static function normalizeColorRows(array $rows): array
    {
        return collect($rows)
            ->filter(fn ($row): bool => is_array($row))
            ->map(function (array $row): ?array {
                $nameEn = filled($row['name_en'] ?? null) ? trim((string) $row['name_en']) : null;
                $nameAr = filled($row['name_ar'] ?? null) ? trim((string) $row['name_ar']) : null;

                if ($nameEn === null && $nameAr === null) {
                    return null;
                }

                $key = filled($row['key'] ?? null)
                    ? Str::slug((string) $row['key'])
                    : Str::slug((string) ($nameEn ?: $nameAr ?: Str::random(8)));

                $hex = filled($row['hex'] ?? null) ? strtoupper(trim((string) $row['hex'])) : null;

                return [
                    'key' => $key !== '' ? $key : Str::random(8),
                    'name_en' => $nameEn,
                    'name_ar' => $nameAr,
                    'hex' => $hex,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('product')
                ->persistTabInQueryString()
                ->tabs([
                    Tabs\Tab::make(__('admin.products.tabs.general'))
                        ->schema([
                            Section::make(__('admin.products.sections.core'))
                                ->description(__('admin.products.sections.core_description'))
                                ->columns(2)
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label(__('admin.products.fields.name'))
                                        ->required()
                                        ->maxLength(255)
                                        ->placeholder(__('admin.products.placeholders.name')),
                                    Forms\Components\TextInput::make('slug')
                                        ->label(__('admin.common.fields.slug'))
                                        ->required()
                                        ->maxLength(255)
                                        ->unique(ignoreRecord: true)
                                        ->placeholder(__('admin.products.placeholders.slug')),
                                    Forms\Components\Select::make('category_id')
                                        ->label(__('admin.products.fields.category'))
                                        ->options(fn (): array => Category::query()
                                            ->orderBy('sort_order')
                                            ->orderBy('name')
                                            ->get()
                                            ->mapWithKeys(fn (Category $category): array => [$category->id => $category->display_name])
                                            ->all())
                                        ->searchable()
                                        ->required(),
                                    Forms\Components\TextInput::make('sku')
                                        ->label(__('admin.products.fields.sku'))
                                        ->maxLength(255)
                                        ->placeholder(__('admin.products.placeholders.sku')),
                                    Forms\Components\Textarea::make('short_description')
                                        ->label(__('admin.products.fields.short_description'))
                                        ->columnSpanFull()
                                        ->rows(3)
                                        ->placeholder(__('admin.products.placeholders.short_description'))
                                        ->maxLength(65535),
                                    Forms\Components\Textarea::make('description')
                                        ->label(__('admin.products.fields.description'))
                                        ->columnSpanFull()
                                        ->rows(5)
                                        ->placeholder(__('admin.products.placeholders.description'))
                                        ->maxLength(65535),
                                ]),
                            Section::make(__('admin.products.sections.specifications'))
                                ->description(__('admin.products.sections.specifications_description'))
                                ->schema([
                                    Forms\Components\Repeater::make('specifications')
                                        ->label(__('admin.products.fields.specifications'))
                                        ->columnSpanFull()
                                        ->reorderable()
                                        ->collapsible()
                                        ->itemLabel(fn (array $state): ?string => $state['label_en'] ?? $state['label_ar'] ?? null)
                                        ->schema([
                                            Forms\Components\TextInput::make('key')
                                                ->label(__('admin.products.fields.specification_key'))
                                                ->placeholder(__('admin.products.placeholders.specification_key'))
                                                ->maxLength(80),
                                            Forms\Components\TextInput::make('label_en')
                                                ->label(__('admin.common.fields.label_en'))
                                                ->placeholder(__('admin.products.placeholders.specification_label_en'))
                                                ->maxLength(120),
                                            Forms\Components\TextInput::make('label_ar')
                                                ->label(__('admin.common.fields.label_ar'))
                                                ->placeholder(__('admin.products.placeholders.specification_label_ar'))
                                                ->maxLength(120),
                                            Forms\Components\TextInput::make('value_en')
                                                ->label(__('admin.common.fields.value_en'))
                                                ->placeholder(__('admin.products.placeholders.specification_value_en'))
                                                ->maxLength(255),
                                            Forms\Components\TextInput::make('value_ar')
                                                ->label(__('admin.common.fields.value_ar'))
                                                ->placeholder(__('admin.products.placeholders.specification_value_ar'))
                                                ->maxLength(255),
                                        ])
                                        ->helperText(__('admin.products.help.specifications')),
                                ]),
                            Section::make(__('admin.products.sections.color_options'))
                                ->description(__('admin.products.sections.color_options_description'))
                                ->schema([
                                    Forms\Components\Repeater::make('color_options')
                                        ->label(__('admin.products.fields.color_options'))
                                        ->columnSpanFull()
                                        ->reorderable()
                                        ->collapsible()
                                        ->itemLabel(fn (array $state): ?string => $state['name_en'] ?? $state['name_ar'] ?? null)
                                        ->schema([
                                            Forms\Components\TextInput::make('key')
                                                ->label(__('admin.products.fields.color_key'))
                                                ->placeholder(__('admin.products.placeholders.color_key'))
                                                ->maxLength(80),
                                            Forms\Components\TextInput::make('name_en')
                                                ->label(__('admin.common.fields.name_en'))
                                                ->placeholder(__('admin.products.placeholders.color_name_en'))
                                                ->maxLength(120),
                                            Forms\Components\TextInput::make('name_ar')
                                                ->label(__('admin.common.fields.name_ar'))
                                                ->placeholder(__('admin.products.placeholders.color_name_ar'))
                                                ->maxLength(120),
                                            Forms\Components\TextInput::make('hex')
                                                ->label(__('admin.products.fields.color_hex'))
                                                ->placeholder('#C89B3C')
                                                ->maxLength(7),
                                        ])
                                        ->helperText(__('admin.products.help.color_options')),
                                ]),
                        ]),
                    Tabs\Tab::make(__('admin.products.tabs.pricing_stock'))
                        ->schema([
                            Section::make(__('admin.products.sections.commerce'))
                                ->description(__('admin.products.sections.commerce_description'))
                                ->columns(2)
                                ->schema([
                                    Forms\Components\Select::make('pricing_type')
                                        ->label(__('admin.products.fields.pricing_type'))
                                        ->options([
                                            'fixed' => __('admin.products.pricing.fixed'),
                                            'estimated' => __('admin.products.pricing.estimated'),
                                        ])
                                        ->required(),
                                    Forms\Components\TextInput::make('price')
                                        ->label(__('admin.products.fields.price'))
                                        ->numeric()
                                        ->required()
                                        ->prefix('MAD'),
                                    Forms\Components\TextInput::make('sale_price')
                                        ->label(__('admin.products.fields.sale_price'))
                                        ->numeric()
                                        ->prefix('MAD')
                                        ->helperText(__('admin.products.help.sale_price')),
                                    Forms\Components\TextInput::make('stock')
                                        ->label(__('admin.products.fields.stock'))
                                        ->integer()
                                        ->minValue(0)
                                        ->default(0)
                                        ->required(),
                                ]),
                        ]),
                    Tabs\Tab::make(__('admin.products.tabs.storefront'))
                        ->schema([
                            Section::make(__('admin.products.sections.storefront'))
                                ->description(__('admin.products.sections.storefront_description'))
                                ->columns(2)
                                ->schema([
                                    Forms\Components\FileUpload::make('main_image')
                                        ->label(__('admin.products.fields.main_image'))
                                        ->image()
                                        ->disk('public')
                                        ->directory('shop/products')
                                        ->visibility('public'),
                                    Forms\Components\FileUpload::make('gallery_images')
                                        ->label(__('admin.products.fields.gallery_images'))
                                        ->image()
                                        ->multiple()
                                        ->reorderable()
                                        ->disk('public')
                                        ->directory('shop/products/gallery')
                                        ->visibility('public')
                                        ->helperText(__('admin.products.help.gallery_images')),
                                    Forms\Components\FileUpload::make('pattern_file_path')
                                        ->label(__('admin.products.fields.pattern_file'))
                                        ->disk('public')
                                        ->directory('shop/products/patterns')
                                        ->visibility('public')
                                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                                        ->helperText(__('admin.products.help.pattern_file')),
                                    Forms\Components\FileUpload::make('pattern_files')
                                        ->label(__('admin.products.fields.pattern_files'))
                                        ->multiple()
                                        ->reorderable()
                                        ->disk('public')
                                        ->directory('shop/products/patterns')
                                        ->visibility('public')
                                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                                        ->helperText(__('admin.products.help.pattern_files')),
                                    Forms\Components\DateTimePicker::make('published_at')
                                        ->label(__('admin.products.fields.published_at')),
                                    Forms\Components\TextInput::make('sort_order')
                                        ->label(__('admin.common.fields.sort_order'))
                                        ->integer()
                                        ->default(0)
                                        ->required()
                                        ->helperText(__('admin.products.help.sort_order')),
                                    Forms\Components\Hidden::make('created_by_admin_id')
                                        ->default(fn (): ?int => auth()->id())
                                        ->dehydrated(fn (?Product $record): bool => $record === null),
                                    Forms\Components\Toggle::make('is_active')
                                        ->label(__('admin.common.fields.is_active'))
                                        ->default(true)
                                        ->inline(false)
                                        ->onColor('primary')
                                        ->offColor('gray'),
                                    Forms\Components\Toggle::make('is_featured')
                                        ->label(__('admin.common.fields.is_featured'))
                                        ->default(false)
                                        ->inline(false)
                                        ->onColor('primary')
                                        ->offColor('gray'),
                                    Forms\Components\Toggle::make('is_best_seller')
                                        ->label(__('admin.common.fields.is_best_seller'))
                                        ->default(false)
                                        ->inline(false)
                                        ->onColor('primary')
                                        ->offColor('gray'),
                                ]),
                        ]),
                    Tabs\Tab::make(__('admin.products.tabs.fabric'))
                        ->schema([
                            Section::make(__('admin.products.sections.fabric_library'))
                                ->description(__('admin.products.sections.fabric_library_description'))
                                ->columns(2)
                                ->schema([
                                    Forms\Components\Select::make('fabric_id')
                                        ->label(__('admin.products.fields.fabric_library'))
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
                                            $record->display_name,
                                            filled($record->country) ? ' ('.$record->country.')' : '',
                                        )))
                                        ->helperText(__('admin.products.help.fabric_library')),
                                    Forms\Components\Placeholder::make('fabric_library_preview')
                                        ->label(__('admin.products.fields.fabric_display_behavior'))
                                        ->content(__('admin.products.help.fabric_display_behavior')),
                                ]),
                            Section::make(__('admin.products.sections.legacy_fabric'))
                                ->description(__('admin.products.sections.legacy_fabric_description'))
                                ->collapsible()
                                ->collapsed()
                                ->columns(2)
                                ->schema([
                                    Forms\Components\TextInput::make('fabric_type')
                                        ->label(__('admin.products.fields.fabric_type'))
                                        ->maxLength(120)
                                        ->placeholder(__('admin.products.placeholders.fabric_type')),
                                    Forms\Components\TextInput::make('fabric_country')
                                        ->label(__('admin.products.fields.fabric_country'))
                                        ->maxLength(120)
                                        ->placeholder(__('admin.products.placeholders.fabric_country')),
                                    Forms\Components\FileUpload::make('fabric_image_path')
                                        ->label(__('admin.products.fields.fabric_image'))
                                        ->image()
                                        ->disk('public')
                                        ->directory('shop/products/fabrics')
                                        ->visibility('public')
                                        ->helperText(__('admin.products.help.fabric_image')),
                                    Forms\Components\Textarea::make('fabric_description')
                                        ->label(__('admin.products.fields.fabric_description'))
                                        ->columnSpanFull()
                                        ->rows(4)
                                        ->maxLength(1000)
                                        ->placeholder(__('admin.products.placeholders.fabric_description')),
                                ]),
                        ]),
                    Tabs\Tab::make(__('admin.products.tabs.measurements'))
                        ->schema([
                            Section::make(__('admin.products.sections.measurements'))
                                ->description(__('admin.products.sections.measurements_description'))
                                ->columns(1)
                                ->schema([
                                    Forms\Components\Select::make('measurements')
                                        ->label(__('admin.products.fields.measurements'))
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
                                        ->helperText(__('admin.products.help.measurements'))
                                        ->getOptionLabelFromRecordUsing(fn (Measurement $record): string => sprintf(
                                            '%s (%s)',
                                            $record->display_name,
                                            MeasurementOptions::formatAudienceLabels($record->normalizedAudiences()),
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
                    ->label(__('admin.products.table.image'))
                    ->square(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.common.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->description(fn (Product $record): ?string => $record->category?->display_name),
                Tables\Columns\TextColumn::make('display_fabric_type')
                    ->label(__('admin.products.table.fabric'))
                    ->state(fn (Product $record): ?string => $record->display_fabric_type)
                    ->description(fn (Product $record): ?string => $record->fabric_id
                        ? __('admin.products.sources.library')
                        : (filled($record->display_fabric_type) ? __('admin.products.sources.legacy_fallback') : null))
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('display_fabric_country')
                    ->label(__('admin.products.table.fabric_country'))
                    ->state(fn (Product $record): ?string => $record->display_fabric_country)
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('pricing_type')
                    ->label(__('admin.products.table.pricing'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'fixed'
                        ? __('admin.products.pricing.fixed')
                        : __('admin.products.pricing.estimated'))
                    ->color(fn (string $state): string => $state === 'fixed' ? 'primary' : 'warning'),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('admin.products.fields.price'))
                    ->money('MAD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale_price')
                    ->label(__('admin.products.fields.sale_price'))
                    ->money('MAD')
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label(__('admin.products.fields.stock'))
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('stock_state')
                    ->label(__('admin.products.table.stock_status'))
                    ->state(fn (Product $record): int => (int) $record->stock)
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => AdminUiState::stockLevelLabel($state))
                    ->color(fn (int $state): string => AdminUiState::stockLevelColor($state)),
                Tables\Columns\TextColumn::make('sku')
                    ->label(__('admin.products.fields.sku'))
                    ->toggleable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('is_active')
                    ->label(__('admin.common.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => AdminUiState::toggleStatusLabel($state))
                    ->color(fn (bool $state): string => AdminUiState::toggleStatusColor($state))
                    ->icon(fn (bool $state): string => $state ? 'heroicon-m-check-circle' : 'heroicon-m-pause-circle'),
                Tables\Columns\TextColumn::make('is_featured')
                    ->label(__('admin.common.fields.is_featured'))
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? __('admin.status.featured') : __('admin.status.standard'))
                    ->color(fn (bool $state): string => $state ? 'warning' : 'gray')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('is_best_seller')
                    ->label(__('admin.common.fields.is_best_seller'))
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? __('admin.products.states.best_seller') : __('admin.products.states.regular'))
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label(__('admin.products.table.published'))
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('admin.common.fields.sort_order'))
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('createdByAdmin.name')
                    ->label(__('admin.products.table.creator'))
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('measurements_count')
                    ->label(__('admin.products.table.measurements'))
                    ->badge()
                    ->color('info')
                    ->toggleable(),
            ])
            ->filters([
                Filter::make('category_id')
                    ->form([
                        Forms\Components\Select::make('value')
                            ->label(__('admin.products.filters.category'))
                            ->options(fn (): array => Category::query()
                                ->orderBy('sort_order')
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (Category $category): array => [$category->id => $category->display_name])
                                ->all()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->where('category_id', $data['value']);
                    }),
                Tables\Filters\SelectFilter::make('pricing_type')
                    ->label(__('admin.products.filters.pricing'))
                    ->options([
                        'fixed' => __('admin.products.pricing.fixed'),
                        'estimated' => __('admin.products.pricing.estimated'),
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')->label(__('admin.products.filters.active')),
                Tables\Filters\TernaryFilter::make('is_featured')->label(__('admin.products.filters.featured')),
                Tables\Filters\TernaryFilter::make('is_best_seller')->label(__('admin.products.filters.best_seller')),
                Filter::make('low_stock')
                    ->label(__('admin.products.filters.low_stock'))
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
