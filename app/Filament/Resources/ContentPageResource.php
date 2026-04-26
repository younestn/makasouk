<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentPageResource\Pages;
use App\Models\ContentPage;
use App\Support\Filament\AdminUiState;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ContentPageResource extends Resource
{
    protected static ?string $model = ContentPage::class;

    protected static ?string $navigationGroup = 'Content';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Legal Pages';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.resources.content_pages');
    }

    public static function getModelLabel(): string
    {
        return __('admin.models.content_page');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.models.content_pages');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Publishing')
                ->description('Control the public URL and footer visibility for this legal/content page.')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->maxLength(160)
                        ->alphaDash()
                        ->unique(ignoreRecord: true)
                        ->helperText('Public URL: /pages/{slug}'),
                    Forms\Components\Select::make('placement')
                        ->options([
                            'footer' => 'Footer',
                            'legal' => 'Legal',
                            'support' => 'Support',
                        ])
                        ->default('footer')
                        ->required(),
                    Forms\Components\TextInput::make('sort_order')
                        ->integer()
                        ->default(0)
                        ->required(),
                    Forms\Components\Toggle::make('show_in_footer')
                        ->default(true)
                        ->inline(false),
                    Forms\Components\Toggle::make('is_published')
                        ->default(false)
                        ->inline(false),
                    Forms\Components\DateTimePicker::make('published_at')
                        ->helperText('Leave empty to publish immediately when status is enabled.'),
                ]),
            Tabs::make('Localized Content')
                ->tabs([
                    Tabs\Tab::make('English')
                        ->schema([
                            Forms\Components\TextInput::make('title_en')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (?string $state, Forms\Set $set, ?ContentPage $record): void {
                                    if ($record === null && filled($state)) {
                                        $set('slug', Str::slug((string) $state));
                                    }
                                }),
                            Forms\Components\TextInput::make('excerpt_en')
                                ->maxLength(255),
                            Forms\Components\RichEditor::make('body_en')
                                ->columnSpanFull()
                                ->toolbarButtons([
                                    'bold',
                                    'italic',
                                    'bulletList',
                                    'orderedList',
                                    'link',
                                    'h2',
                                    'h3',
                                    'blockquote',
                                    'undo',
                                    'redo',
                                ]),
                        ]),
                    Tabs\Tab::make('Arabic')
                        ->schema([
                            Forms\Components\TextInput::make('title_ar')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('excerpt_ar')
                                ->maxLength(255),
                            Forms\Components\RichEditor::make('body_ar')
                                ->columnSpanFull()
                                ->toolbarButtons([
                                    'bold',
                                    'italic',
                                    'bulletList',
                                    'orderedList',
                                    'link',
                                    'h2',
                                    'h3',
                                    'blockquote',
                                    'undo',
                                    'redo',
                                ]),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title_en')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->description(fn (ContentPage $record): string => '/pages/'.$record->slug),
                Tables\Columns\TextColumn::make('placement')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => str($state)->headline()->toString())
                    ->color('primary'),
                Tables\Columns\TextColumn::make('show_in_footer')
                    ->label('Footer')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Shown' : 'Hidden')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('is_published')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => AdminUiState::toggleStatusLabel($state))
                    ->color(fn (bool $state): string => AdminUiState::toggleStatusColor($state)),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->since()
                    ->label('Updated')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')->label('Published'),
                Tables\Filters\TernaryFilter::make('show_in_footer')->label('Shown in footer'),
                Tables\Filters\SelectFilter::make('placement')
                    ->options([
                        'footer' => 'Footer',
                        'legal' => 'Legal',
                        'support' => 'Support',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContentPages::route('/'),
            'create' => Pages\CreateContentPage::route('/create'),
            'edit' => Pages\EditContentPage::route('/{record}/edit'),
        ];
    }
}
