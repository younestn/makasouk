<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapProviderSetting extends Model
{
    use HasFactory;

    public const PROVIDER_OPENSTREETMAP = 'openstreetmap';
    public const PROVIDER_MAPBOX = 'mapbox';
    public const PROVIDER_CUSTOM = 'custom';

    public const GEOCODER_NOMINATIM = 'nominatim';
    public const GEOCODER_MAPBOX = 'mapbox';
    public const GEOCODER_NONE = 'none';

    public const ALGERIA_CENTER_LATITUDE = 28.0339;
    public const ALGERIA_CENTER_LONGITUDE = 1.6596;

    /**
     * @var array<int, string>
     */
    public const PROVIDERS = [
        self::PROVIDER_OPENSTREETMAP,
        self::PROVIDER_MAPBOX,
        self::PROVIDER_CUSTOM,
    ];

    /**
     * @var array<int, string>
     */
    public const GEOCODERS = [
        self::GEOCODER_NOMINATIM,
        self::GEOCODER_MAPBOX,
        self::GEOCODER_NONE,
    ];

    protected $fillable = [
        'is_enabled',
        'active_provider',
        'provider_token',
        'tile_url_template',
        'attribution',
        'geocoder_provider',
        'geocoding_url_template',
        'default_latitude',
        'default_longitude',
        'default_zoom',
        'min_zoom',
        'max_zoom',
        'south_west_latitude',
        'south_west_longitude',
        'north_east_latitude',
        'north_east_longitude',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'provider_token' => 'encrypted',
            'default_latitude' => 'float',
            'default_longitude' => 'float',
            'south_west_latitude' => 'float',
            'south_west_longitude' => 'float',
            'north_east_latitude' => 'float',
            'north_east_longitude' => 'float',
            'default_zoom' => 'integer',
            'min_zoom' => 'integer',
            'max_zoom' => 'integer',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], self::defaults());
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'is_enabled' => true,
            'active_provider' => self::PROVIDER_OPENSTREETMAP,
            'tile_url_template' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            'attribution' => '&copy; OpenStreetMap contributors',
            'geocoder_provider' => self::GEOCODER_NOMINATIM,
            'geocoding_url_template' => 'https://nominatim.openstreetmap.org/reverse?format=jsonv2&addressdetails=1&lat={lat}&lon={lng}&accept-language={locale}',
            'default_latitude' => self::ALGERIA_CENTER_LATITUDE,
            'default_longitude' => self::ALGERIA_CENTER_LONGITUDE,
            'default_zoom' => 5,
            'min_zoom' => 5,
            'max_zoom' => 18,
            'south_west_latitude' => 18.9,
            'south_west_longitude' => -8.7,
            'north_east_latitude' => 37.2,
            'north_east_longitude' => 12.1,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function providerOptions(): array
    {
        return [
            self::PROVIDER_OPENSTREETMAP => __('admin.maps.providers.openstreetmap'),
            self::PROVIDER_MAPBOX => __('admin.maps.providers.mapbox'),
            self::PROVIDER_CUSTOM => __('admin.maps.providers.custom'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function geocoderOptions(): array
    {
        return [
            self::GEOCODER_NOMINATIM => __('admin.maps.geocoders.nominatim'),
            self::GEOCODER_MAPBOX => __('admin.maps.geocoders.mapbox'),
            self::GEOCODER_NONE => __('admin.maps.geocoders.none'),
        ];
    }

    public function resolvedTileUrl(): string
    {
        $template = filled($this->tile_url_template)
            ? (string) $this->tile_url_template
            : (string) self::defaults()['tile_url_template'];

        if ($this->active_provider === self::PROVIDER_MAPBOX && ! str_contains($template, 'mapbox.com')) {
            $template = 'https://api.mapbox.com/styles/v1/mapbox/streets-v12/tiles/256/{z}/{x}/{y}@2x?access_token={token}';
        }

        return strtr($template, [
            '{token}' => (string) $this->provider_token,
        ]);
    }

    public function resolvedGeocodingUrl(): ?string
    {
        if ($this->geocoder_provider === self::GEOCODER_NONE) {
            return null;
        }

        if ($this->geocoder_provider === self::GEOCODER_MAPBOX && blank($this->provider_token)) {
            return null;
        }

        $template = filled($this->geocoding_url_template)
            ? (string) $this->geocoding_url_template
            : (string) self::defaults()['geocoding_url_template'];

        if ($this->geocoder_provider === self::GEOCODER_MAPBOX) {
            $template = 'https://api.mapbox.com/geocoding/v5/mapbox.places/{lng},{lat}.json?country=dz&types=region,place,locality,address&language={locale}&access_token={token}';
        }

        return strtr($template, [
            '{token}' => (string) $this->provider_token,
        ]);
    }
}
