<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentUsersTable extends TableWidget
{
    protected int | string | array $columnSpan = [
        'default' => 1,
        'xl' => 3,
    ];

    protected function getTableHeading(): ?string
    {
        return 'Recent Users';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query()->latest()->limit(8))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->limit(24),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        User::ROLE_ADMIN => 'primary',
                        User::ROLE_CUSTOMER => 'success',
                        User::ROLE_TAILOR => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_suspended')
                    ->label('Suspended')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->label('Joined'),
            ])
            ->recordUrl(fn (User $record): string => UserResource::getUrl('view', ['record' => $record]))
            ->emptyStateHeading('No users yet')
            ->emptyStateDescription('Recently registered users will be listed here.');
    }
}
