<?php

use App\Support\Tailor\MeasurementOptions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('measurements', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('audience', 32)->default(MeasurementOptions::AUDIENCE_UNISEX)->index();
            $table->text('description')->nullable();
            $table->text('guide_text')->nullable();
            $table->string('helper_text', 255)->nullable();
            $table->string('guide_image_path')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('measurements');
    }
};
