<?php

namespace App\Filament\Pages;

use App\Models\MapProviderSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class MapProviderSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.map-provider-settings';

    public ?array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.pages.map_provider_settings');
    }

    public function getTitle(): string
    {
        return __('admin.maps.title');
    }

    public function getSubheading(): ?string
    {
        return __('admin.maps.subheading');
    }

    public function mount(): void
    {
        $settings = MapProviderSetting::current();

        $this->form->fill([
            ...$settings->toArray(),
            'provider_token' => null,
        ]);
    }

    public function form(Forms\Form $form): Forms\Form
    {
        $settings = MapProviderSetting::current();

        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\Section::make(__('admin.maps.sections.general'))
                    ->description(__('admin.maps.sections.general_description'))
                    ->icon('heroicon-o-map')
                    ->columns(3)
                    ->schema([
                        Forms\Components\Toggle::make('is_enabled')
                            ->label(__('admin.maps.fields.is_enabled'))
                            ->default(true)
                            ->inline(false),
                        Forms\Components\Select::make('active_provider')
                            ->label(__('admin.maps.fields.active_provider'))
                            ->options(MapProviderSetting::providerOptions())
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('geocoder_provider')
                            ->label(__('admin.maps.fields.geocoder_provider'))
                            ->options(MapProviderSetting::geocoderOptions())
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('provider_token')
                            ->label(__('admin.maps.fields.provider_token'))
                            ->password()
                            ->revealable()
                            ->placeholder($settings->provider_token ? __('admin.maps.placeholders.token_saved') : null)
                            ->helperText(__('admin.maps.help.provider_token'))
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('tile_url_template')
                            ->label(__('admin.maps.fields.tile_url_template'))
                            ->helperText(__('admin.maps.help.tile_url_template'))
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('geocoding_url_template')
                            ->label(__('admin.maps.fields.geocoding_url_template'))
                            ->helperText(__('admin.maps.help.geocoding_url_template'))
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('attribution')
                            ->label(__('admin.maps.fields.attribution'))
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make(__('admin.maps.sections.algeria'))
                    ->description(__('admin.maps.sections.algeria_description'))
                    ->icon('heroicon-o-map-pin')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('default_latitude')
                            ->label(__('admin.maps.fields.default_latitude'))
                            ->numeric()
                            ->minValue(-90)
                            ->maxValue(90)
                            ->required(),
                        Forms\Components\TextInput::make('default_longitude')
                            ->label(__('admin.maps.fields.default_longitude'))
                            ->numeric()
                            ->minValue(-180)
                            ->maxValue(180)
                            ->required(),
                        Forms\Components\TextInput::make('default_zoom')
                            ->label(__('admin.maps.fields.default_zoom'))
                            ->integer()
                            ->minValue(3)
                            ->maxValue(18)
                            ->required(),
                        Forms\Components\TextInput::make('min_zoom')
                            ->label(__('admin.maps.fields.min_zoom'))
                            ->integer()
                            ->minValue(1)
                            ->maxValue(18)
                            ->required(),
                        Forms\Components\TextInput::make('max_zoom')
                            ->label(__('admin.maps.fields.max_zoom'))
                            ->integer()
                            ->minValue(5)
                            ->maxValue(22)
                            ->required(),
                        Forms\Components\Placeholder::make('algeria_notice')
                            ->label(__('admin.maps.fields.algeria_notice'))
                            ->content(__('admin.maps.help.algeria_notice')),
                    ]),
                Forms\Components\Section::make(__('admin.maps.sections.bounds'))
                    ->description(__('admin.maps.sections.bounds_description'))
                    ->icon('heroicon-o-arrows-pointing-out')
                    ->columns(4)
                    ->schema([
                        Forms\Components\TextInput::make('south_west_latitude')
                            ->label(__('admin.maps.fields.south_west_latitude'))
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('south_west_longitude')
                            ->label(__('admin.maps.fields.south_west_longitude'))
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('north_east_latitude')
                            ->label(__('admin.maps.fields.north_east_latitude'))
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('north_east_longitude')
                            ->label(__('admin.maps.fields.north_east_longitude'))
                            ->numeric()
                            ->required(),
                    ]),
            ]);
    }

    public function save(): void
    {
        $settings = MapProviderSetting::current();
        $data = $this->form->getState();

        if (blank($data['provider_token'] ?? null)) {
            unset($data['provider_token']);
        }

        $settings->fill($data)->save();
        $this->mount();

        Notification::make()
            ->title(__('admin.maps.notifications.saved'))
            ->success()
            ->send();
    }
}
