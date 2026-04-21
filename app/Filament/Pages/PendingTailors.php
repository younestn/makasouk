<?php

namespace App\Filament\Pages;

use App\Filament\Resources\UserResource;
use App\Models\TailorProfile;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class PendingTailors extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationGroup = 'Users';

    protected static ?string $navigationLabel = 'Pending Tailors';

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Pending Tailor Approvals';

    protected static string $view = 'filament.pages.pending-tailors';

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
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('tailorProfile.category.name')
                    ->label('Category')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('tailorProfile.status')
                    ->badge()
                    ->label('Profile Status')
                    ->placeholder('offline'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Registered')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve Tailor')
                    ->icon('heroicon-o-check-circle')
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
                    ->url(fn (User $record): string => UserResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateHeading('No pending tailor approvals')
            ->emptyStateDescription('New tailor applications that require approval will appear here.')
            ->emptyStateIcon('heroicon-o-check-badge');
    }
}
