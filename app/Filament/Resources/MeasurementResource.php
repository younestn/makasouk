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

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Measurement Definition')
                ->description('Define reusable body measurement fields for customer intake forms.')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(120)
                        ->placeholder('Chest'),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->maxLength(160)
                        ->alphaDash()
                        ->unique(ignoreRecord: true)
                        ->placeholder('chest')
                        ->helperText('Internal key used in order measurement payloads.'),
                    Forms\Components\Select::make('audience')
                        ->options(MeasurementOptions::audienceOptions())
                        ->required()
                        ->default(MeasurementOptions::AUDIENCE_UNISEX),
                    Forms\Components\TextInput::make('sort_order')
                        ->integer()
                        ->required()
                        ->default(0)
                        ->helperText('Lower numbers appear first in product order forms.'),
                    Forms\Components\Textarea::make('description')
                        ->columnSpanFull()
                        ->rows(2)
                        ->maxLength(65535)
                        ->placeholder('Optional context shown to admins.'),
                    Forms\Components\Textarea::make('guide_text')
                        ->columnSpanFull()
                        ->rows(4)
                        ->maxLength(65535)
                        ->placeholder('Explain clearly how customers should measure this body part.'),
                    Forms\Components\TextInput::make('helper_text')
                        ->columnSpanFull()
                        ->maxLength(255)
                        ->placeholder('Enter value in centimeters (cm).'),
                ]),
            Section::make('Guide Media & Status')
                ->description('Optional visual guidance and publish controls.')
                ->columns(2)
                ->schema([
                    Forms\Components\FileUpload::make('guide_image_path')
                        ->label('Guide Image')
                        ->image()
                        ->disk('public')
                        ->directory('measurements/guides')
                        ->visibility('public'),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->inline(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount('products'))
            ->columns([
                Tables\Columns\ImageColumn::make('guide_image_path')
                    ->label('Guide')
                    ->disk('public')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('audience')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => str((string) $state)->headline()->toString())
                    ->color('primary'),
                Tables\Columns\TextColumn::make('helper_text')
                    ->label('Helper')
                    ->limit(35)
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => AdminUiState::toggleStatusLabel($state))
                    ->color(fn (bool $state): string => AdminUiState::toggleStatusColor($state))
                    ->icon(fn (bool $state): string => $state ? 'heroicon-m-check-circle' : 'heroicon-m-pause-circle'),
                Tables\Columns\TextColumn::make('products_count')
                    ->label('Products')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->since()
                    ->label('Updated')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('audience')
                    ->options(MeasurementOptions::audienceOptions())
                    ->label('Audience'),
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
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
}
