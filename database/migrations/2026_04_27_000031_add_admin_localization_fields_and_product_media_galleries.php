<?php

use App\Support\Tailor\MeasurementOptions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('measurements', function (Blueprint $table): void {
            $table->string('name_en')->nullable()->after('name');
            $table->string('name_ar')->nullable()->after('name_en');
            $table->json('audiences')->nullable()->after('audience');
            $table->text('description_en')->nullable()->after('description');
            $table->text('description_ar')->nullable()->after('description_en');
            $table->text('guide_text_en')->nullable()->after('guide_text');
            $table->text('guide_text_ar')->nullable()->after('guide_text_en');
            $table->string('helper_text_en', 255)->nullable()->after('helper_text');
            $table->string('helper_text_ar', 255)->nullable()->after('helper_text_en');
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->string('name_en')->nullable()->after('name');
            $table->string('name_ar')->nullable()->after('name_en');
            $table->text('description_en')->nullable()->after('description');
            $table->text('description_ar')->nullable()->after('description_en');
        });

        Schema::table('fabrics', function (Blueprint $table): void {
            $table->string('name_en')->nullable()->after('name');
            $table->string('name_ar')->nullable()->after('name_en');
            $table->text('description_en')->nullable()->after('description');
            $table->text('description_ar')->nullable()->after('description_en');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->json('gallery_images')->nullable()->after('main_image');
            $table->json('pattern_files')->nullable()->after('pattern_file_path');
        });

        DB::table('measurements')->orderBy('id')->get()->each(function (object $measurement): void {
            $audiences = match ($measurement->audience) {
                MeasurementOptions::AUDIENCE_WOMEN => [MeasurementOptions::AUDIENCE_WOMEN],
                MeasurementOptions::AUDIENCE_MEN => [MeasurementOptions::AUDIENCE_MEN],
                MeasurementOptions::AUDIENCE_CHILDREN => [MeasurementOptions::AUDIENCE_CHILDREN],
                default => MeasurementOptions::selectableAudiences(),
            };

            DB::table('measurements')
                ->where('id', $measurement->id)
                ->update([
                    'name_en' => $measurement->name,
                    'name_ar' => null,
                    'audiences' => json_encode($audiences, JSON_UNESCAPED_UNICODE),
                    'description_en' => $measurement->description,
                    'description_ar' => null,
                    'guide_text_en' => $measurement->guide_text,
                    'guide_text_ar' => null,
                    'helper_text_en' => $measurement->helper_text,
                    'helper_text_ar' => null,
                ]);
        });

        DB::table('categories')->orderBy('id')->get()->each(function (object $category): void {
            DB::table('categories')
                ->where('id', $category->id)
                ->update([
                    'name_en' => $category->name,
                    'name_ar' => null,
                    'description_en' => $category->description,
                    'description_ar' => null,
                ]);
        });

        DB::table('fabrics')->orderBy('id')->get()->each(function (object $fabric): void {
            DB::table('fabrics')
                ->where('id', $fabric->id)
                ->update([
                    'name_en' => $fabric->name,
                    'name_ar' => null,
                    'description_en' => $fabric->description,
                    'description_ar' => null,
                ]);
        });

        DB::table('products')->orderBy('id')->get()->each(function (object $product): void {
            DB::table('products')
                ->where('id', $product->id)
                ->update([
                    'gallery_images' => filled($product->main_image)
                        ? json_encode([$product->main_image], JSON_UNESCAPED_UNICODE)
                        : null,
                    'pattern_files' => filled($product->pattern_file_path)
                        ? json_encode([$product->pattern_file_path], JSON_UNESCAPED_UNICODE)
                        : null,
                ]);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['gallery_images', 'pattern_files']);
        });

        Schema::table('fabrics', function (Blueprint $table): void {
            $table->dropColumn(['name_en', 'name_ar', 'description_en', 'description_ar']);
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropColumn(['name_en', 'name_ar', 'description_en', 'description_ar']);
        });

        Schema::table('measurements', function (Blueprint $table): void {
            $table->dropColumn([
                'name_en',
                'name_ar',
                'audiences',
                'description_en',
                'description_ar',
                'guide_text_en',
                'guide_text_ar',
                'helper_text_en',
                'helper_text_ar',
            ]);
        });
    }
};
