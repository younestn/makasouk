<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\TailorProfile;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'Users';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Users Directory';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('role')
                    ->options([
                        User::ROLE_ADMIN => 'Admin',
                        User::ROLE_CUSTOMER => 'Customer',
                        User::ROLE_TAILOR => 'Tailor',
                    ])
                    ->disabled(),
                Forms\Components\Toggle::make('is_suspended')
                    ->label('Suspended')
                    ->disabled(),
                Forms\Components\DateTimePicker::make('approved_at')
                    ->label('Approved At')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('tailorProfile.category'))
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        User::ROLE_ADMIN => 'primary',
                        User::ROLE_CUSTOMER => 'success',
                        User::ROLE_TAILOR => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_suspended')->boolean()->label('Suspended'),
                Tables\Columns\TextColumn::make('approved_at')->dateTime()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('tailorProfile.category.name')
                    ->label('Tailor Category')
                    ->placeholder('-')
                    ->toggleable(),
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
                Filter::make('approved_tailors')
                    ->label('Approved Tailors')
                    ->query(fn (Builder $query): Builder => $query->where('role', User::ROLE_TAILOR)->whereNotNull('approved_at')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve_tailor')
                    ->label('Approve Tailor')
                    ->visible(fn (User $record): bool => $record->role === User::ROLE_TAILOR && $record->approved_at === null)
                    ->action(function (User $record): void {
                        DB::transaction(function () use ($record): void {
                            $record->forceFill(['approved_at' => now()])->save();

                            if ($record->tailorProfile !== null) {
                                $record->tailorProfile->forceFill(['status' => TailorProfile::STATUS_OFFLINE])->save();
                            }
                        });

                        Notification::make()
                            ->title('Tailor approved successfully')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('suspend')
                    ->label('Suspend')
                    ->visible(fn (User $record): bool => ! $record->is_suspended && $record->role !== User::ROLE_ADMIN)
                    ->action(function (User $record): void {
                        DB::transaction(function () use ($record): void {
                            $record->forceFill(['is_suspended' => true])->save();

                            if ($record->role === User::ROLE_TAILOR && $record->tailorProfile !== null) {
                                $record->tailorProfile->forceFill(['status' => TailorProfile::STATUS_OFFLINE])->save();
                            }
                        });

                        Notification::make()
                            ->title('User suspended successfully')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('unsuspend')
                    ->label('Unsuspend')
                    ->visible(fn (User $record): bool => $record->is_suspended)
                    ->action(function (User $record): void {
                        $record->forceFill(['is_suspended' => false])->save();

                        Notification::make()
                            ->title('User unsuspended successfully')
                            ->success()
                            ->send();
                    })
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
