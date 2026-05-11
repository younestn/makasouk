<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_companies', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->string('name_ar')->nullable();
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->json('specifications')->nullable()->after('description');
            $table->json('color_options')->nullable()->after('specifications');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->json('order_configuration')->nullable()->after('measurements');
            $table->foreignId('shipping_company_id')
                ->nullable()
                ->after('delivery_location_label')
                ->constrained('shipping_companies')
                ->nullOnDelete();
            $table->string('shipping_company_name')->nullable()->after('shipping_company_id');
            $table->string('delivery_type')->default('office_pickup')->after('shipping_company_name');
            $table->string('delivery_commune')->nullable()->after('delivery_work_wilaya');
            $table->string('delivery_neighborhood')->nullable()->after('delivery_commune');
            $table->string('delivery_phone', 40)->nullable()->after('delivery_neighborhood');
            $table->string('delivery_email')->nullable()->after('delivery_phone');
        });

        $defaultCompanies = [
            [
                'name' => 'Yalidine',
                'name_en' => 'Yalidine',
                'name_ar' => 'ياليدين',
                'code' => 'yalidine',
                'description' => 'Office pickup courier service.',
                'description_en' => 'Office pickup courier service.',
                'description_ar' => 'خدمة شحن مع استلام من المكتب فقط.',
                'sort_order' => 1,
            ],
            [
                'name' => 'ZR Express',
                'name_en' => 'ZR Express',
                'name_ar' => 'زد آر إكسبريس',
                'code' => 'zr-express',
                'description' => 'Regional delivery network for bureau pickup.',
                'description_en' => 'Regional delivery network for bureau pickup.',
                'description_ar' => 'شبكة توصيل جهوية مع استلام من المكتب.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Noest',
                'name_en' => 'Noest',
                'name_ar' => 'نوست',
                'code' => 'noest',
                'description' => 'Courier option for office-only delivery workflow.',
                'description_en' => 'Courier option for office-only delivery workflow.',
                'description_ar' => 'شركة شحن مناسبة لآلية التسليم عبر المكتب فقط.',
                'sort_order' => 3,
            ],
        ];

        foreach ($defaultCompanies as $company) {
            DB::table('shipping_companies')->updateOrInsert(
                ['code' => $company['code']],
                array_merge($company, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]),
            );
        }

        DB::table('products')->orderBy('id')->get()->each(function (object $product): void {
            if (filled($product->specifications) || filled($product->color_options)) {
                return;
            }

            $specifications = [];

            if (filled($product->fabric_type)) {
                $specifications[] = [
                    'key' => 'fabric_type',
                    'label_en' => 'Fabric Type',
                    'label_ar' => 'نوع القماش',
                    'value_en' => $product->fabric_type,
                    'value_ar' => $product->fabric_type,
                ];
            }

            if (filled($product->fabric_country)) {
                $specifications[] = [
                    'key' => 'fabric_origin',
                    'label_en' => 'Origin',
                    'label_ar' => 'بلد المنشأ',
                    'value_en' => $product->fabric_country,
                    'value_ar' => $product->fabric_country,
                ];
            }

            DB::table('products')
                ->where('id', $product->id)
                ->update([
                    'specifications' => $specifications === [] ? null : json_encode($specifications, JSON_UNESCAPED_UNICODE),
                    'color_options' => null,
                ]);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (Schema::hasColumn('orders', 'shipping_company_id')) {
                $table->dropConstrainedForeignId('shipping_company_id');
            }

            $table->dropColumn([
                'order_configuration',
                'shipping_company_name',
                'delivery_type',
                'delivery_commune',
                'delivery_neighborhood',
                'delivery_phone',
                'delivery_email',
            ]);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['specifications', 'color_options']);
        });

        Schema::dropIfExists('shipping_companies');
    }
};