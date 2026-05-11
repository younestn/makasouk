<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShippingCompanyResource\Pages;
use App\Models\ShippingCompany;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShippingCompanyResource extends Resource
{
    protected static ?string $model = ShippingCompany::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?int $navigationSort = 30;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.shipping_companies.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.shipping_companies.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.shipping_companies.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.shipping_companies.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('admin.shipping_companies.sections.identity'))
                ->description(__('admin.shipping_companies.sections.identity_description'))
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name_en')
                        ->label(__('admin.shipping_companies.fields.name_en'))
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('name_ar')
                        ->label(__('admin.shipping_companies.fields.name_ar'))
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('name')
                        ->label(__('admin.shipping_companies.fields.name_fallback'))
                        ->helperText(__('admin.shipping_companies.help.name_fallback'))
                        ->maxLength(255),
                    Forms\Components\TextInput::make('code')
                        ->label(__('admin.shipping_companies.fields.code'))
                        ->required()
                        ->maxLength(100)
                        ->unique(ignoreRecord: true),
                    Forms\Components\Textarea::make('description_en')
                        ->label(__('admin.shipping_companies.fields.description_en'))
                        ->rows(3),
                    Forms\Components\Textarea::make('description_ar')
                        ->label(__('admin.shipping_companies.fields.description_ar'))
                        ->rows(3),
                    Forms\Components\Textarea::make('description')
                        ->label(__('admin.shipping_companies.fields.description_fallback'))
                        ->rows(3)
                        ->helperText(__('admin.shipping_companies.help.description_fallback')),
                    Forms\Components\TextInput::make('sort_order')
                        ->label(__('admin.shipping_companies.fields.sort_order'))
                        ->numeric()
                        ->default(0)
                        ->required(),
                    Forms\Components\Toggle::make('is_active')
                        ->label(__('admin.shipping_companies.fields.is_active'))
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
            ->columns([
                Tables\Columns\TextColumn::make('display_name')
                    ->label(__('admin.shipping_companies.columns.name'))
                    ->searchable(['name', 'name_en', 'name_ar', 'code'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('admin.shipping_companies.columns.code'))
                    ->badge()
                    ->color('gray'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('admin.shipping_companies.columns.is_active'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('admin.shipping_companies.columns.sort_order'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.shipping_companies.columns.updated_at'))
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShippingCompanies::route('/'),
            'create' => Pages\CreateShippingCompany::route('/create'),
            'edit' => Pages\EditShippingCompany::route('/{record}/edit'),
        ];
    }
}
