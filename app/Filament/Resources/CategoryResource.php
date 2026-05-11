<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use App\Support\Filament\AdminUiState;
use App\Support\Tailor\TailorOnboardingOptions;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.catalog');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.resources.categories');
    }

    public static function getModelLabel(): string
    {
        return __('admin.models.category');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.models.categories');
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
            Section::make(__('admin.categories.sections.profile'))
                ->description(__('admin.categories.sections.profile_description'))
                ->icon('heroicon-o-tag')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name_en')
                        ->label(__('admin.common.fields.name_en'))
                        ->rules(['required_without:name_ar'])
                        ->maxLength(255)
                        ->placeholder(__('admin.categories.placeholders.name_en'))
                        ->live(onBlur: true)
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
                        ->placeholder(__('admin.categories.placeholders.name_ar'))
                        ->extraInputAttributes(['dir' => 'rtl']),
                    Forms\Components\TextInput::make('slug')
                        ->label(__('admin.common.fields.slug'))
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->placeholder(__('admin.categories.placeholders.slug'))
                        ->helperText(__('admin.categories.help.slug')),
                    Forms\Components\Select::make('tailor_specialization')
                        ->label(__('admin.categories.fields.tailor_specialization'))
                        ->options(TailorOnboardingOptions::specializationOptions())
                        ->searchable()
                        ->required(),
                    Forms\Components\Textarea::make('description_en')
                        ->label(__('admin.common.fields.description_en'))
                        ->columnSpanFull()
                        ->rows(3)
                        ->maxLength(65535)
                        ->placeholder(__('admin.categories.placeholders.description_en')),
                    Forms\Components\Textarea::make('description_ar')
                        ->label(__('admin.common.fields.description_ar'))
                        ->columnSpanFull()
                        ->rows(3)
                        ->maxLength(65535)
                        ->placeholder(__('admin.categories.placeholders.description_ar'))
                        ->extraInputAttributes(['dir' => 'rtl']),
                ]),
            Section::make(__('admin.categories.sections.storefront'))
                ->description(__('admin.categories.sections.storefront_description'))
                ->icon('heroicon-o-adjustments-horizontal')
                ->columns(2)
                ->schema([
                    Forms\Components\FileUpload::make('image_path')
                        ->label(__('admin.categories.fields.image'))
                        ->image()
                        ->disk('public')
                        ->directory('shop/categories')
                        ->visibility('public'),
                    Forms\Components\TextInput::make('sort_order')
                        ->label(__('admin.common.fields.sort_order'))
                        ->integer()
                        ->default(0)
                        ->required()
                        ->helperText(__('admin.categories.help.sort_order')),
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
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount(['products', 'tailorProfiles']))
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')->disk('public')->label(__('admin.common.fields.image')),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.common.fields.name'))
                    ->state(fn (Category $record): string => $record->display_name)
                    ->searchable(['name', 'name_en', 'name_ar'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('admin.common.fields.slug'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tailor_specialization')
                    ->label(__('admin.categories.table.specialization'))
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('admin.common.fields.description'))
                    ->state(fn (Category $record): ?string => $record->display_description)
                    ->limit(60)
                    ->placeholder('-')
                    ->toggleable(),
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
                    ->color(fn (bool $state): string => $state ? 'warning' : 'gray'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('admin.common.fields.sort_order'))
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('products_count')
                    ->label(__('admin.categories.table.products'))
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tailor_profiles_count')
                    ->label(__('admin.categories.table.tailor_profiles'))
                    ->badge()
                    ->color('info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->since()
                    ->label(__('admin.common.fields.updated'))
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label(__('admin.categories.filters.active')),
                Tables\Filters\TernaryFilter::make('is_featured')->label(__('admin.categories.filters.featured')),
                Tables\Filters\SelectFilter::make('tailor_specialization')
                    ->label(__('admin.categories.filters.tailor_specialization'))
                    ->options(TailorOnboardingOptions::specializationOptions()),
            ])
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye'),
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil-square'),
                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->visible(fn (Category $record): bool => $record->products_count === 0 && $record->tailor_profiles_count === 0),
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'view' => Pages\ViewCategory::route('/{record}'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
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
