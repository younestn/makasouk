<?php

use App\Models\MapProviderSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('map_provider_settings', function (Blueprint $table): void {
            $table->id();
            $table->boolean('is_enabled')->default(true);
            $table->string('active_provider', 32)->default(MapProviderSetting::PROVIDER_OPENSTREETMAP);
            $table->text('provider_token')->nullable();
            $table->text('tile_url_template')->nullable();
            $table->text('attribution')->nullable();
            $table->string('geocoder_provider', 32)->default(MapProviderSetting::GEOCODER_NOMINATIM);
            $table->text('geocoding_url_template')->nullable();
            $table->decimal('default_latitude', 10, 7)->default(MapProviderSetting::ALGERIA_CENTER_LATITUDE);
            $table->decimal('default_longitude', 10, 7)->default(MapProviderSetting::ALGERIA_CENTER_LONGITUDE);
            $table->unsignedTinyInteger('default_zoom')->default(5);
            $table->unsignedTinyInteger('min_zoom')->default(5);
            $table->unsignedTinyInteger('max_zoom')->default(18);
            $table->decimal('south_west_latitude', 10, 7)->default(18.9);
            $table->decimal('south_west_longitude', 10, 7)->default(-8.7);
            $table->decimal('north_east_latitude', 10, 7)->default(37.2);
            $table->decimal('north_east_longitude', 10, 7)->default(12.1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('map_provider_settings');
    }
};
