<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\TailorProfile;
use App\Models\User;
use App\Support\Filament\AdminUiState;
use App\Support\Tailor\TailorOnboardingOptions;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

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
                Section::make('Account')
                    ->description('Identity and role profile for this platform user.')
                    ->icon('heroicon-o-identification')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(32)
                            ->disabled()
                            ->placeholder('-'),
                        Forms\Components\Select::make('role')
                            ->options([
                                User::ROLE_ADMIN => 'Admin',
                                User::ROLE_CUSTOMER => 'Customer',
                                User::ROLE_TAILOR => 'Tailor',
                            ])
                            ->formatStateUsing(fn (?string $state): string => AdminUiState::userRoleLabel($state))
                            ->disabled(),
                    ]),
                Section::make('Status')
                    ->description('Operational access and tailor verification state.')
                    ->icon('heroicon-o-shield-check')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Toggle::make('is_suspended')
                            ->label('Suspended')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('approved_at')
                            ->label('Tailor Approved At')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('phone_verified_at')
                            ->label('Phone Verified At')
                            ->disabled(),
                    ]),
                Section::make('Tailor Professional Profile')
                    ->description('Captured during tailor onboarding and used for matching/routing.')
                    ->icon('heroicon-o-briefcase')
                    ->visible(fn (?User $record): bool => $record?->role === User::ROLE_TAILOR)
                    ->columns(2)
                    ->schema([
                        Forms\Components\Placeholder::make('tailor_profile_specialization')
                            ->label('Specialization')
                            ->content(fn (?User $record): string => $record?->tailorProfile?->specialization ?? '-'),
                        Forms\Components\Placeholder::make('tailor_profile_work_wilaya')
                            ->label('Work Wilaya')
                            ->content(fn (?User $record): string => $record?->tailorProfile?->work_wilaya ?? '-'),
                        Forms\Components\Placeholder::make('tailor_profile_years_of_experience')
                            ->label('Years of Experience')
                            ->content(fn (?User $record): string => (string) ($record?->tailorProfile?->years_of_experience ?? '-')),
                        Forms\Components\Placeholder::make('tailor_profile_workers_count')
                            ->label('Workers Count')
                            ->content(fn (?User $record): string => (string) ($record?->tailorProfile?->workers_count ?? '-')),
                        Forms\Components\Placeholder::make('tailor_profile_latitude')
                            ->label('Latitude')
                            ->content(fn (?User $record): string => (string) ($record?->tailorProfile?->latitude ?? '-')),
                        Forms\Components\Placeholder::make('tailor_profile_longitude')
                            ->label('Longitude')
                            ->content(fn (?User $record): string => (string) ($record?->tailorProfile?->longitude ?? '-')),
                        Forms\Components\Placeholder::make('tailor_profile_gender')
                            ->label('Gender')
                            ->content(fn (?User $record): string => $record?->tailorProfile?->gender ?? '-'),
                        Forms\Components\Placeholder::make('tailorProfile.commercial_register_path')
                            ->label('Commercial Register Document')
                            ->content(function (?User $record): HtmlString {
                                $path = $record?->tailorProfile?->commercial_register_path;

                                if (blank($path)) {
                                    return new HtmlString('<span class="text-gray-500">Not uploaded</span>');
                                }

                                $url = e(Storage::disk('public')->url($path));

                                return new HtmlString("<a href=\"{$url}\" target=\"_blank\" class=\"text-primary-600 underline\">Open uploaded file</a>");
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('tailorProfile.category'))
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable()->sortable(),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Email Verified')
                    ->boolean()
                    ->getStateUsing(fn (User $record): bool => $record->email_verified_at !== null)
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->copyable()
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => AdminUiState::userRoleLabel($state))
                    ->color(fn (?string $state): string => AdminUiState::userRoleColor($state)),
                Tables\Columns\IconColumn::make('phone_verified_at')
                    ->label('Phone Verified')
                    ->boolean()
                    ->getStateUsing(fn (User $record): bool => $record->phone_verified_at !== null)
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('is_suspended')
                    ->label('Suspension')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Suspended' : 'Active')
                    ->color(fn (bool $state): string => $state ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Tailor Approval')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => filled($state) ? 'Approved' : 'Awaiting Review')
                    ->color(fn ($state): string => filled($state) ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('tailorProfile.status')
                    ->label('Tailor Profile')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => AdminUiState::tailorProfileStatusLabel($state))
                    ->color(fn (?string $state): string => AdminUiState::tailorProfileStatusColor($state))
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y H:i')
                    ->label('Joined')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tailorProfile.category.name')
                    ->label('Tailor Category')
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tailorProfile.specialization')
                    ->label('Specialization')
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tailorProfile.work_wilaya')
                    ->label('Work Wilaya')
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tailorProfile.latitude')
                    ->label('Latitude')
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tailorProfile.longitude')
                    ->label('Longitude')
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tailorProfile.years_of_experience')
                    ->label('Experience (Years)')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tailorProfile.workers_count')
                    ->label('Workers')
                    ->placeholder('-')
                    ->sortable()
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
                Tables\Filters\TernaryFilter::make('phone_verified')
                    ->label('Phone Verified')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('phone_verified_at'),
                        false: fn (Builder $query): Builder => $query->whereNull('phone_verified_at'),
                        blank: fn (Builder $query): Builder => $query,
                    ),
                Tables\Filters\TernaryFilter::make('email_verified')
                    ->label('Email Verified')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('email_verified_at'),
                        false: fn (Builder $query): Builder => $query->whereNull('email_verified_at'),
                        blank: fn (Builder $query): Builder => $query,
                    ),
                Tables\Filters\SelectFilter::make('tailorProfile.specialization')
                    ->label('Specialization')
                    ->options(TailorOnboardingOptions::specializationOptions()),
                Tables\Filters\SelectFilter::make('tailorProfile.work_wilaya')
                    ->label('Work Wilaya')
                    ->options(TailorOnboardingOptions::wilayaOptions()),
            ])
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye'),
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil-square'),
                Tables\Actions\Action::make('approve_tailor')
                    ->label('Approve Tailor')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
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
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
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
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->visible(fn (User $record): bool => $record->is_suspended)
                    ->action(function (User $record): void {
                        $record->forceFill(['is_suspended' => false])->save();

                        Notification::make()
                            ->title('User unsuspended successfully')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
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
