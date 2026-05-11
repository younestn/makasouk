<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FabricResource\Pages;
use App\Models\Fabric;
use App\Support\Filament\AdminUiState;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class FabricResource extends Resource
{
    protected static ?string $model = Fabric::class;

    protected static ?string $navigationIcon = 'heroicon-o-swatch';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.catalog');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.resources.fabrics');
    }

    public static function getModelLabel(): string
    {
        return __('admin.models.fabric');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.models.fabrics');
    }

    public static function normalizeFormData(array $data): array
    {
        $nameEn = self::normalizeNullableString($data['name_en'] ?? null);
        $nameAr = self::normalizeNullableString($data['name_ar'] ?? null);
        $descriptionEn = self::normalizeNullableString($data['description_en'] ?? null);
        $descriptionAr = self::normalizeNullableString($data['description_ar'] ?? null);

        $data['name_en'] = $nameEn;
        $data['name_ar'] = $nameAr;
        $data['name'] = $nameEn ?? $nameAr ?? self::normalizeNullableString($data['name'] ?? null);
        $data['description_en'] = $descriptionEn;
        $data['description_ar'] = $descriptionAr;
        $data['description'] = $descriptionEn ?? $descriptionAr;
        $data['country'] = self::normalizeNullableString($data['country'] ?? null);
        $data['reference_code'] = self::normalizeNullableString($data['reference_code'] ?? null);
        $data['notes'] = self::normalizeNullableString($data['notes'] ?? null);

        if (blank($data['slug'] ?? null) && filled($nameEn ?? $data['name'] ?? null)) {
            $slug = Str::slug((string) ($nameEn ?? $data['name']));

            if (filled($slug)) {
                $data['slug'] = $slug;
            }
        }

        return $data;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('admin.fabrics.sections.identity'))
                ->description(__('admin.fabrics.sections.identity_description'))
                ->icon('heroicon-o-swatch')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name_en')
                        ->label(__('admin.common.fields.name_en'))
                        ->rules(['required_without:name_ar'])
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->placeholder(__('admin.fabrics.placeholders.name_en'))
                        ->afterStateUpdated(function (?string $state, Forms\Set $set, Forms\Get $get): void {
                            if (blank($get('slug')) && filled($state)) {
                                $slug = Str::slug($state);

                                if (filled($slug)) {
                                    $set('slug', $slug);
                                }
                            }
                        }),
                    Forms\Components\TextInput::make('name_ar')
                        ->label(__('admin.common.fields.name_ar'))
                        ->rules(['required_without:name_en'])
                        ->maxLength(255)
                        ->placeholder(__('admin.fabrics.placeholders.name_ar'))
                        ->extraInputAttributes(['dir' => 'rtl']),
                    Forms\Components\TextInput::make('slug')
                        ->label(__('admin.common.fields.slug'))
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->helperText(__('admin.fabrics.help.slug')),
                    Forms\Components\TextInput::make('country')
                        ->label(__('admin.fabrics.fields.country'))
                        ->maxLength(120)
                        ->placeholder(__('admin.fabrics.placeholders.country')),
                    Forms\Components\TextInput::make('reference_code')
                        ->label(__('admin.fabrics.fields.reference_code'))
                        ->maxLength(80)
                        ->placeholder(__('admin.fabrics.placeholders.reference_code')),
                    Forms\Components\FileUpload::make('image_path')
                        ->label(__('admin.fabrics.fields.image'))
                        ->image()
                        ->disk('public')
                        ->directory('shop/fabrics')
                        ->visibility('public')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('description_en')
                        ->label(__('admin.common.fields.description_en'))
                        ->rows(4)
                        ->maxLength(2000)
                        ->placeholder(__('admin.fabrics.placeholders.description_en')),
                    Forms\Components\Textarea::make('description_ar')
                        ->label(__('admin.common.fields.description_ar'))
                        ->rows(4)
                        ->maxLength(2000)
                        ->placeholder(__('admin.fabrics.placeholders.description_ar'))
                        ->extraInputAttributes(['dir' => 'rtl']),
                    Forms\Components\Textarea::make('notes')
                        ->label(__('admin.fabrics.fields.notes'))
                        ->rows(3)
                        ->maxLength(2000)
                        ->columnSpanFull()
                        ->helperText(__('admin.fabrics.help.notes')),
                ]),
            Section::make(__('admin.fabrics.sections.publishing'))
                ->description(__('admin.fabrics.sections.publishing_description'))
                ->icon('heroicon-o-adjustments-horizontal')
                ->columns(2)
                ->schema([
                    Forms\Components\Toggle::make('is_active')
                        ->label(__('admin.common.fields.is_active'))
                        ->default(true)
                        ->inline(false)
                        ->onColor('primary')
                        ->offColor('gray'),
                    Forms\Components\TextInput::make('sort_order')
                        ->label(__('admin.common.fields.sort_order'))
                        ->integer()
                        ->default(0)
                        ->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount('products'))
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->disk('public')
                    ->label(__('admin.common.fields.preview'))
                    ->square(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.common.fields.name'))
                    ->state(fn (Fabric $record): string => $record->display_name)
                    ->searchable(['name', 'name_en', 'name_ar'])
                    ->sortable()
                    ->description(fn (Fabric $record): ?string => $record->reference_code),
                Tables\Columns\TextColumn::make('country')
                    ->label(__('admin.fabrics.table.origin'))
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('products_count')
                    ->label(__('admin.fabrics.table.products'))
                    ->badge()
                    ->color('info')
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\TextColumn::make('is_active')
                    ->label(__('admin.common.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => AdminUiState::toggleStatusLabel($state))
                    ->color(fn (bool $state): string => AdminUiState::toggleStatusColor($state)),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('admin.common.fields.sort_order'))
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.common.fields.updated'))
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label(__('admin.fabrics.filters.active')),
                Tables\Filters\Filter::make('used_by_products')
                    ->label(__('admin.fabrics.filters.used_by_products'))
                    ->query(fn (Builder $query): Builder => $query->has('products')),
            ])
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
            ->defaultSort('sort_order')
            ->paginated([10, 25, 50]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFabrics::route('/'),
            'create' => Pages\CreateFabric::route('/create'),
            'edit' => Pages\EditFabric::route('/{record}/edit'),
        ];
    }

    private static function normalizeNullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        return $normalized === '' ? null : $normalized;
    }
}
