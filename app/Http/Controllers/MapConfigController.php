<?php

namespace App\Http\Controllers;

use App\Models\MapProviderSetting;
use Illuminate\Http\JsonResponse;

class MapConfigController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $settings = MapProviderSetting::current();
        $defaults = MapProviderSetting::defaults();
        $enabled = (bool) $settings->is_enabled;
        $canUseConfiguredProvider = $enabled
            && ! (
                ($settings->active_provider === MapProviderSetting::PROVIDER_MAPBOX && blank($settings->provider_token))
                || ($settings->active_provider === MapProviderSetting::PROVIDER_CUSTOM && blank($settings->tile_url_template))
            );

        return response()->json([
            'data' => [
                'enabled' => $canUseConfiguredProvider,
                'provider' => $canUseConfiguredProvider ? $settings->active_provider : MapProviderSetting::PROVIDER_OPENSTREETMAP,
                'tile_url' => $canUseConfiguredProvider ? $settings->resolvedTileUrl() : (string) $defaults['tile_url_template'],
                'attribution' => $settings->attribution ?: (string) $defaults['attribution'],
                'geocoder' => [
                    'provider' => $settings->geocoder_provider ?: MapProviderSetting::GEOCODER_NOMINATIM,
                    'reverse_url' => $canUseConfiguredProvider ? $settings->resolvedGeocodingUrl() : (string) $defaults['geocoding_url_template'],
                    'country_code' => 'dz',
                ],
                'algeria' => [
                    'center' => [
                        'latitude' => (float) ($settings->default_latitude ?: $defaults['default_latitude']),
                        'longitude' => (float) ($settings->default_longitude ?: $defaults['default_longitude']),
                    ],
                    'zoom' => (int) ($settings->default_zoom ?: $defaults['default_zoom']),
                    'min_zoom' => (int) ($settings->min_zoom ?: $defaults['min_zoom']),
                    'max_zoom' => (int) ($settings->max_zoom ?: $defaults['max_zoom']),
                    'bounds' => [
                        'south_west' => [
                            'latitude' => (float) ($settings->south_west_latitude ?: $defaults['south_west_latitude']),
                            'longitude' => (float) ($settings->south_west_longitude ?: $defaults['south_west_longitude']),
                        ],
                        'north_east' => [
                            'latitude' => (float) ($settings->north_east_latitude ?: $defaults['north_east_latitude']),
                            'longitude' => (float) ($settings->north_east_longitude ?: $defaults['north_east_longitude']),
                        ],
                    ],
                ],
            ],
        ]);
    }
}
