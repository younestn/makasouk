<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'Backoffice';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('email')->email()->required()->maxLength(255),
                Forms\Components\Select::make('role')->options([
                    User::ROLE_ADMIN => 'Admin',
                    User::ROLE_CUSTOMER => 'Customer',
                    User::ROLE_TAILOR => 'Tailor',
                ])->required(),
                Forms\Components\Toggle::make('is_suspended')->label('Suspended'),
                Forms\Components\DateTimePicker::make('approved_at')->label('Approved At'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable()->sortable(),
                Tables\Columns\BadgeColumn::make('role')->colors([
                    'primary' => User::ROLE_ADMIN,
                    'success' => User::ROLE_CUSTOMER,
                    'warning' => User::ROLE_TAILOR,
                ]),
                Tables\Columns\IconColumn::make('is_suspended')->boolean()->label('Suspended'),
                Tables\Columns\TextColumn::make('approved_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('tailorProfile.category.name')->label('Tailor Category'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')->options([
                    User::ROLE_ADMIN => 'Admin',
                    User::ROLE_CUSTOMER => 'Customer',
                    User::ROLE_TAILOR => 'Tailor',
                ]),
                Tables\Filters\TernaryFilter::make('is_suspended')->label('Suspended'),
                Filter::make('pending_tailors')
                    ->label('Pending Tailors')
                    ->query(fn (Builder $query): Builder => $query->where('role', User::ROLE_TAILOR)->whereNull('approved_at')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve_tailor')
                    ->label('Approve Tailor')
                    ->visible(fn (User $record): bool => $record->role === User::ROLE_TAILOR && $record->approved_at === null)
                    ->action(function (User $record): void {
                        $record->update(['approved_at' => now()]);
                        $record->tailorProfile?->update(['status' => 'offline']);
                    })
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('suspend')
                    ->label('Suspend')
                    ->visible(fn (User $record): bool => ! $record->is_suspended)
                    ->disabled(fn (User $record): bool => auth()->id() === $record->id)
                    ->action(fn (User $record) => $record->update(['is_suspended' => true]))
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('unsuspend')
                    ->label('Unsuspend')
                    ->visible(fn (User $record): bool => $record->is_suspended)
                    ->action(fn (User $record) => $record->update(['is_suspended' => false]))
                    ->requiresConfirmation(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
