<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MeasurementResource\Pages;
use App\Models\Measurement;
use App\Support\Filament\AdminUiState;
use App\Support\Tailor\MeasurementOptions;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class MeasurementResource extends Resource
{
    protected static ?string $model = Measurement::class;

    protected static ?string $navigationGroup = 'Catalog';

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationLabel = 'Measurements';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.catalog');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.resources.measurements');
    }

    public static function getModelLabel(): string
    {
        return __('admin.models.measurement');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.models.measurements');
    }

    public static function normalizeFormData(array $data): array
    {
        $nameEn = self::normalizeNullableString($data['name_en'] ?? null);
        $nameAr = self::normalizeNullableString($data['name_ar'] ?? null);
        $descriptionEn = self::normalizeNullableString($data['description_en'] ?? null);
        $descriptionAr = self::normalizeNullableString($data['description_ar'] ?? null);
        $guideTextEn = self::normalizeNullableString($data['guide_text_en'] ?? null);
        $guideTextAr = self::normalizeNullableString($data['guide_text_ar'] ?? null);
        $helperTextEn = self::normalizeNullableString($data['helper_text_en'] ?? null);
        $helperTextAr = self::normalizeNullableString($data['helper_text_ar'] ?? null);
        $audiences = MeasurementOptions::normalizeAudiences($data['audiences'] ?? null, $data['audience'] ?? null);

        $data['name_en'] = $nameEn;
        $data['name_ar'] = $nameAr;
        $data['name'] = $nameEn ?? $nameAr ?? self::normalizeNullableString($data['name'] ?? null);
        $data['description_en'] = $descriptionEn;
        $data['description_ar'] = $descriptionAr;
        $data['description'] = $descriptionEn ?? $descriptionAr;
        $data['guide_text_en'] = $guideTextEn;
        $data['guide_text_ar'] = $guideTextAr;
        $data['guide_text'] = $guideTextEn ?? $guideTextAr;
        $data['helper_text_en'] = $helperTextEn;
        $data['helper_text_ar'] = $helperTextAr;
        $data['helper_text'] = $helperTextEn ?? $helperTextAr;
        $data['audiences'] = $audiences;
        $data['audience'] = MeasurementOptions::legacyAudienceFromAudiences($audiences);

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
            Section::make(__('admin.measurements.sections.definition'))
                ->description(__('admin.measurements.sections.definition_description'))
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name_en')
                        ->label(__('admin.common.fields.name_en'))
                        ->rules(['required_without:name_ar'])
                        ->maxLength(120)
                        ->placeholder(__('admin.measurements.placeholders.name_en'))
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
                        ->maxLength(120)
                        ->placeholder(__('admin.measurements.placeholders.name_ar'))
                        ->extraInputAttributes(['dir' => 'rtl']),
                    Forms\Components\TextInput::make('slug')
                        ->label(__('admin.common.fields.slug'))
                        ->required()
                        ->maxLength(160)
                        ->alphaDash()
                        ->unique(ignoreRecord: true)
                        ->placeholder(__('admin.measurements.placeholders.slug'))
                        ->helperText(__('admin.measurements.help.slug')),
                    Forms\Components\TextInput::make('sort_order')
                        ->label(__('admin.common.fields.sort_order'))
                        ->integer()
                        ->required()
                        ->default(0)
                        ->helperText(__('admin.measurements.help.sort_order')),
                    Forms\Components\CheckboxList::make('audiences')
                        ->label(__('admin.measurements.fields.audiences'))
                        ->options(MeasurementOptions::audienceOptions())
                        ->columns(3)
                        ->required()
                        ->default(MeasurementOptions::selectableAudiences())
                        ->columnSpanFull(),
                ]),
            Section::make(__('admin.measurements.sections.localized_content'))
                ->description(__('admin.measurements.sections.localized_content_description'))
                ->columns(2)
                ->schema([
                    Forms\Components\Textarea::make('description_en')
                        ->label(__('admin.common.fields.description_en'))
                        ->rows(2)
                        ->maxLength(65535)
                        ->placeholder(__('admin.measurements.placeholders.description_en')),
                    Forms\Components\Textarea::make('description_ar')
                        ->label(__('admin.common.fields.description_ar'))
                        ->rows(2)
                        ->maxLength(65535)
                        ->placeholder(__('admin.measurements.placeholders.description_ar'))
                        ->extraInputAttributes(['dir' => 'rtl']),
                    Forms\Components\Textarea::make('guide_text_en')
                        ->label(__('admin.measurements.fields.guide_text_en'))
                        ->rows(4)
                        ->maxLength(65535)
                        ->placeholder(__('admin.measurements.placeholders.guide_text_en')),
                    Forms\Components\Textarea::make('guide_text_ar')
                        ->label(__('admin.measurements.fields.guide_text_ar'))
                        ->rows(4)
                        ->maxLength(65535)
                        ->placeholder(__('admin.measurements.placeholders.guide_text_ar'))
                        ->extraInputAttributes(['dir' => 'rtl']),
                    Forms\Components\TextInput::make('helper_text_en')
                        ->label(__('admin.measurements.fields.helper_text_en'))
                        ->maxLength(255)
                        ->placeholder(__('admin.measurements.placeholders.helper_text_en')),
                    Forms\Components\TextInput::make('helper_text_ar')
                        ->label(__('admin.measurements.fields.helper_text_ar'))
                        ->maxLength(255)
                        ->placeholder(__('admin.measurements.placeholders.helper_text_ar'))
                        ->extraInputAttributes(['dir' => 'rtl']),
                ]),
            Section::make(__('admin.measurements.sections.media_status'))
                ->description(__('admin.measurements.sections.media_status_description'))
                ->columns(2)
                ->schema([
                    Forms\Components\FileUpload::make('guide_image_path')
                        ->label(__('admin.measurements.fields.guide_image'))
                        ->image()
                        ->disk('public')
                        ->directory('measurements/guides')
                        ->visibility('public'),
                    Forms\Components\Toggle::make('is_active')
                        ->label(__('admin.common.fields.is_active'))
                        ->default(true)
                        ->inline(false)
                        ->onColor('primary')
                        ->offColor('gray'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount('products'))
            ->columns([
                Tables\Columns\ImageColumn::make('guide_image_path')
                    ->label(__('admin.measurements.table.guide'))
                    ->disk('public')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.common.fields.name'))
                    ->state(fn (Measurement $record): string => $record->display_name)
                    ->searchable(['name', 'name_en', 'name_ar'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('admin.common.fields.slug'))
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('audiences')
                    ->label(__('admin.measurements.fields.audiences'))
                    ->badge()
                    ->state(fn (Measurement $record): string => $record->display_audience_labels)
                    ->color('primary'),
                Tables\Columns\TextColumn::make('helper_text')
                    ->label(__('admin.measurements.table.helper'))
                    ->state(fn (Measurement $record): ?string => $record->display_helper_text)
                    ->limit(35)
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('is_active')
                    ->label(__('admin.common.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => AdminUiState::toggleStatusLabel($state))
                    ->color(fn (bool $state): string => AdminUiState::toggleStatusColor($state))
                    ->icon(fn (bool $state): string => $state ? 'heroicon-m-check-circle' : 'heroicon-m-pause-circle'),
                Tables\Columns\TextColumn::make('products_count')
                    ->label(__('admin.measurements.table.products'))
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('admin.common.fields.sort_order'))
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->since()
                    ->label(__('admin.common.fields.updated'))
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('audience')
                    ->form([
                        Forms\Components\Select::make('value')
                            ->label(__('admin.measurements.filters.audience'))
                            ->options(MeasurementOptions::audienceOptions()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->forAudience($data['value']);
                    }),
                Tables\Filters\TernaryFilter::make('is_active')->label(__('admin.measurements.filters.active')),
            ])
            ->filtersFormColumns(2)
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
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMeasurements::route('/'),
            'create' => Pages\CreateMeasurement::route('/create'),
            'view' => Pages\ViewMeasurement::route('/{record}'),
            'edit' => Pages\EditMeasurement::route('/{record}/edit'),
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
