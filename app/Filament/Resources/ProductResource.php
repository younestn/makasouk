<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationGroup = 'Catalog';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('category_id')->relationship('category', 'name')->required()->searchable(),
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true),
            Forms\Components\Textarea::make('description'),
            Forms\Components\Select::make('pricing_type')->options(['fixed' => 'Fixed', 'estimated' => 'Estimated'])->required(),
            Forms\Components\TextInput::make('price')->numeric()->required(),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('category.name')->sortable(),
            Tables\Columns\TextColumn::make('pricing_type')->badge(),
            Tables\Columns\TextColumn::make('price')->money('USD'),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
            Tables\Columns\TextColumn::make('createdByAdmin.name')->label('Creator'),
        ])->filters([
            Tables\Filters\SelectFilter::make('category_id')->options(Category::query()->pluck('name', 'id')),
            Tables\Filters\TernaryFilter::make('is_active'),
        ])->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_admin_id'] = auth()->id();
        return $data;
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
