<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fabrics', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('country')->nullable();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->string('reference_code')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->foreignId('fabric_id')
                ->nullable()
                ->after('fabric_image_path')
                ->constrained('fabrics')
                ->nullOnDelete();
        });

        $this->backfillLegacyProductFabrics();
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('fabric_id');
        });

        Schema::dropIfExists('fabrics');
    }

    private function backfillLegacyProductFabrics(): void
    {
        $seen = [];

        DB::table('products')
            ->select(['id', 'fabric_type', 'fabric_country', 'fabric_description', 'fabric_image_path'])
            ->where(function ($query): void {
                $query
                    ->whereNotNull('fabric_type')
                    ->orWhereNotNull('fabric_country')
                    ->orWhereNotNull('fabric_description')
                    ->orWhereNotNull('fabric_image_path');
            })
            ->orderBy('id')
            ->get()
            ->each(function ($product) use (&$seen): void {
                $name = filled($product->fabric_type) ? (string) $product->fabric_type : 'Fabric '.$product->id;
                $country = $product->fabric_country !== null ? (string) $product->fabric_country : null;
                $description = $product->fabric_description !== null ? (string) $product->fabric_description : null;
                $imagePath = $product->fabric_image_path !== null ? (string) $product->fabric_image_path : null;

                $key = sha1(strtolower(trim($name)).'|'.strtolower(trim((string) $country)).'|'.trim((string) $description).'|'.trim((string) $imagePath));

                if (! isset($seen[$key])) {
                    $seen[$key] = DB::table('fabrics')->insertGetId([
                        'name' => $name,
                        'slug' => $this->uniqueSlug($name, $product->id),
                        'country' => $country,
                        'description' => $description,
                        'image_path' => $imagePath,
                        'is_active' => true,
                        'sort_order' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('products')
                    ->where('id', $product->id)
                    ->update([
                        'fabric_id' => $seen[$key],
                        'updated_at' => now(),
                    ]);
            });
    }

    private function uniqueSlug(string $name, int $productId): string
    {
        $base = Str::slug($name) ?: 'fabric';
        $slug = $base;
        $suffix = 1;

        while (DB::table('fabrics')->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$productId.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
};
