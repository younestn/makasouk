<?php

namespace App\Filament\Pages;

use App\Filament\Resources\UserResource;
use App\Models\TailorProfile;
use App\Models\User;
use App\Support\Filament\AdminUiState;
use App\Support\Tailor\TailorOnboardingOptions;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PendingTailors extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationGroup = 'Users';

    protected static ?string $navigationLabel = 'Pending Tailors';

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Pending Tailor Approvals';

    protected static string $view = 'filament.pages.pending-tailors';

    public function getSubheading(): ?string
    {
        return 'Approve tailor onboarding requests after reviewing category fit and profile readiness.';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->where('role', User::ROLE_TAILOR)
                    ->whereNull('approved_at')
                    ->with(['tailorProfile.category'])
                    ->latest(),
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tailor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email copied'),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Email Verified')
                    ->boolean()
                    ->getStateUsing(fn (User $record): bool => $record->email_verified_at !== null)
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('success')
                    ->falseColor('warning'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Phone copied')
                    ->placeholder('-'),
                Tables\Columns\IconColumn::make('phone_verified_at')
                    ->label('Phone Verified')
                    ->boolean()
                    ->getStateUsing(fn (User $record): bool => $record->phone_verified_at !== null)
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('success')
                    ->falseColor('warning'),
                Tables\Columns\TextColumn::make('tailorProfile.category.name')
                    ->label('Category')
                    ->placeholder('-')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('tailorProfile.specialization')
                    ->label('Specialization')
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tailorProfile.work_wilaya')
                    ->label('Work Wilaya')
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tailorProfile.years_of_experience')
                    ->label('Experience')
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tailorProfile.workers_count')
                    ->label('Workers')
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
                Tables\Columns\TextColumn::make('tailorProfile.status')
                    ->badge()
                    ->label('Profile Status')
                    ->formatStateUsing(fn (?string $state): string => AdminUiState::tailorProfileStatusLabel($state))
                    ->color(fn (?string $state): string => AdminUiState::tailorProfileStatusColor($state))
                    ->placeholder('Offline'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y H:i')
                    ->label('Registered')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('registered_last_7_days')
                    ->label('Registered Last 7 Days')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', '>=', now()->subDays(7))),
                Filter::make('phone_verified')
                    ->label('Phone Verified')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('phone_verified_at')),
                Filter::make('email_verified')
                    ->label('Email Verified')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('email_verified_at')),
                Tables\Filters\SelectFilter::make('tailorProfile.specialization')
                    ->label('Specialization')
                    ->options(TailorOnboardingOptions::specializationOptions()),
                Tables\Filters\SelectFilter::make('tailorProfile.work_wilaya')
                    ->label('Work Wilaya')
                    ->options(TailorOnboardingOptions::wilayaOptions()),
            ])
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve Tailor')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->action(function (User $record): void {
                        DB::transaction(function () use ($record): void {
                            $record->forceFill(['approved_at' => now()])->save();

                            if ($record->tailorProfile !== null) {
                                $record->tailorProfile->forceFill(['status' => TailorProfile::STATUS_OFFLINE])->save();
                            }
                        });

                        Notification::make()
                            ->title('Tailor approved successfully.')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('open_user')
                    ->label('Open User')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (User $record): string => UserResource::getUrl('view', ['record' => $record])),
                Tables\Actions\Action::make('commercial_register')
                    ->label('Commercial Register')
                    ->icon('heroicon-o-document-text')
                    ->color('gray')
                    ->visible(fn (User $record): bool => filled($record->tailorProfile?->commercial_register_path))
                    ->url(fn (User $record): ?string => filled($record->tailorProfile?->commercial_register_path)
                        ? Storage::disk('public')->url($record->tailorProfile->commercial_register_path)
                        : null)
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('No pending tailor approvals')
            ->emptyStateDescription('New tailor applications that require approval will appear here.')
            ->emptyStateIcon('heroicon-o-check-badge');
    }
}
