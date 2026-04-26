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
        return 'Fabric';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Fabrics';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Fabric Identity')
                ->description('Reusable fabric details that can be assigned to many products.')
                ->icon('heroicon-o-swatch')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Fabric Name / Type')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (?string $state, Forms\Set $set): void {
                            if (filled($state)) {
                                $set('slug', Str::slug($state));
                            }
                        }),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('country')
                        ->label('Origin Country')
                        ->maxLength(120),
                    Forms\Components\TextInput::make('reference_code')
                        ->label('Reference Code')
                        ->maxLength(80),
                    Forms\Components\FileUpload::make('image_path')
                        ->label('Fabric Image')
                        ->image()
                        ->disk('public')
                        ->directory('shop/fabrics')
                        ->visibility('public')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('description')
                        ->rows(4)
                        ->maxLength(2000)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('notes')
                        ->rows(3)
                        ->maxLength(2000)
                        ->columnSpanFull()
                        ->helperText('Internal admin notes. Not shown to customers.'),
                ]),
            Section::make('Publishing')
                ->description('Control availability and ordering in selectors.')
                ->icon('heroicon-o-adjustments-horizontal')
                ->columns(2)
                ->schema([
                    Forms\Components\Toggle::make('is_active')
                        ->default(true)
                        ->inline(false),
                    Forms\Components\TextInput::make('sort_order')
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
                    ->label('Preview')
                    ->square(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Fabric')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Fabric $record): ?string => $record->reference_code),
                Tables\Columns\TextColumn::make('country')
                    ->label('Origin')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('products_count')
                    ->label('Products')
                    ->badge()
                    ->color('info')
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => AdminUiState::toggleStatusLabel($state))
                    ->color(fn (bool $state): string => AdminUiState::toggleStatusColor($state)),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
                Tables\Filters\Filter::make('used_by_products')
                    ->label('Used by products')
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
}
