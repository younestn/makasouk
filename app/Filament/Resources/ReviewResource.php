<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;
    protected static ?string $navigationGroup = 'Operations';
    protected static ?string $navigationIcon = 'heroicon-o-star';

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')->sortable(),
            Tables\Columns\TextColumn::make('order.id')->label('Order'),
            Tables\Columns\TextColumn::make('customer.name')->label('Customer')->searchable(),
            Tables\Columns\TextColumn::make('tailor.name')->label('Tailor')->searchable(),
            Tables\Columns\TextColumn::make('rating')->sortable(),
            Tables\Columns\TextColumn::make('comment')->limit(60),
            Tables\Columns\TextColumn::make('created_at')->dateTime(),
        ])->filters([
            Tables\Filters\SelectFilter::make('rating')->options([1=>1,2=>2,3=>3,4=>4,5=>5]),
            Tables\Filters\SelectFilter::make('tailor_id')->relationship('tailor', 'name'),
        ])->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\DeleteAction::make()->requiresConfirmation(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'view' => Pages\ViewReview::route('/{record}'),
        ];
    }
}
