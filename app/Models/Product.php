<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'created_by_admin_id',
        'name',
        'slug',
        'short_description',
        'description',
        'specifications',
        'color_options',
        'main_image',
        'gallery_images',
        'pattern_file_path',
        'pattern_files',
        'fabric_id',
        'fabric_type',
        'fabric_country',
        'fabric_description',
        'fabric_image_path',
        'pricing_type',
        'price',
        'sale_price',
        'stock',
        'sku',
        'is_active',
        'is_featured',
        'is_best_seller',
        'published_at',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'gallery_images' => 'array',
            'specifications' => 'array',
            'color_options' => 'array',
            'pattern_files' => 'array',
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'published_at' => 'datetime',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_best_seller' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->active()
            ->where(function (Builder $publishedQuery): void {
                $publishedQuery
                    ->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeBestSeller(Builder $query): Builder
    {
        return $query->where('is_best_seller', true);
    }

    public function scopeNewArrivals(Builder $query): Builder
    {
        return $query->published()->latest('published_at')->latest('created_at');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }

    public function fabric(): BelongsTo
    {
        return $this->belongsTo(Fabric::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(
            Review::class,
            Order::class,
            'product_id',
            'order_id',
            'id',
            'id',
        );
    }

    public function measurements(): BelongsToMany
    {
        return $this->belongsToMany(Measurement::class, 'measurement_product')
            ->withTimestamps()
            ->orderBy('measurements.sort_order')
            ->orderBy('measurements.name');
    }

    public function getMainImageUrlAttribute(): ?string
    {
        $primaryPath = $this->main_image
            ?: collect($this->gallery_images ?? [])->filter()->first();

        return $this->resolvePublicAssetUrl($primaryPath);
    }

    /**
     * @return array<int, string>
     */
    public function getGalleryImageUrlsAttribute(): array
    {
        return $this->resolvePublicAssetUrls(array_values(array_unique(array_filter([
            $this->main_image,
            ...($this->gallery_images ?? []),
        ]))));
    }

    public function getFabricImageUrlAttribute(): ?string
    {
        return $this->display_fabric_image_url;
    }

    public function getPatternFileUrlAttribute(): ?string
    {
        return $this->pattern_file_urls[0] ?? null;
    }

    /**
     * @return array<int, string>
     */
    public function getPatternFileUrlsAttribute(): array
    {
        return $this->resolvePublicAssetUrls(array_values(array_unique(array_filter([
            $this->pattern_file_path,
            ...($this->pattern_files ?? []),
        ]))));
    }

    public function getHasPatternFilesAttribute(): bool
    {
        return $this->pattern_file_urls !== [];
    }

    public function getDisplayFabricTypeAttribute(): ?string
    {
        return $this->fabric?->display_name ?? $this->fabric_type;
    }

    public function getDisplayFabricCountryAttribute(): ?string
    {
        return $this->fabric?->country ?? $this->fabric_country;
    }

    public function getDisplayFabricDescriptionAttribute(): ?string
    {
        return $this->fabric?->display_description ?? $this->fabric_description;
    }

    public function getDisplayFabricImageUrlAttribute(): ?string
    {
        return $this->fabric?->image_url ?? $this->resolvePublicAssetUrl($this->fabric_image_path);
    }

    /**
     * @return array<int, array{key: string, label: string, value: string}>
     */
    public function localizedSpecifications(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return collect($this->specifications ?? [])
            ->map(function ($specification) use ($locale): ?array {
                if (! is_array($specification)) {
                    return null;
                }

                $label = $locale === 'ar'
                    ? ($specification['label_ar'] ?? null)
                    : ($specification['label_en'] ?? null);

                $value = $locale === 'ar'
                    ? ($specification['value_ar'] ?? null)
                    : ($specification['value_en'] ?? null);

                $label = filled($label)
                    ? (string) $label
                    : (string) ($specification['label_en'] ?? $specification['label_ar'] ?? '');

                $value = filled($value)
                    ? (string) $value
                    : (string) ($specification['value_en'] ?? $specification['value_ar'] ?? '');

                if ($label === '' || $value === '') {
                    return null;
                }

                return [
                    'key' => (string) ($specification['key'] ?? Str::slug($label)),
                    'label' => $label,
                    'value' => $value,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{key: string, label: string, hex: string|null}>
     */
    public function localizedColorOptions(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return collect($this->color_options ?? [])
            ->map(function ($option) use ($locale): ?array {
                if (! is_array($option)) {
                    return null;
                }

                $label = $locale === 'ar'
                    ? ($option['name_ar'] ?? null)
                    : ($option['name_en'] ?? null);

                $label = filled($label)
                    ? (string) $label
                    : (string) ($option['name_en'] ?? $option['name_ar'] ?? '');

                if ($label === '') {
                    return null;
                }

                $hex = filled($option['hex'] ?? null) ? (string) $option['hex'] : null;

                return [
                    'key' => (string) ($option['key'] ?? Str::slug($label)),
                    'label' => $label,
                    'hex' => $hex,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{key: string, label: string, type: string|null, country: string|null, description: string|null, image_url: string|null, source: string}>
     */
    public function availableFabricOptions(): array
    {
        if (! $this->display_fabric_type && ! $this->display_fabric_country && ! $this->display_fabric_description && ! $this->display_fabric_image_url) {
            return [];
        }

        $label = trim(collect([$this->display_fabric_type, $this->display_fabric_country])->filter()->implode(' - '));

        return [[
            'key' => $this->fabric_id ? 'library:'.$this->fabric_id : 'legacy:primary',
            'label' => $label !== '' ? $label : __('shop.product.fabric_title'),
            'type' => $this->display_fabric_type,
            'country' => $this->display_fabric_country,
            'description' => $this->display_fabric_description,
            'image_url' => $this->display_fabric_image_url,
            'source' => $this->fabric_id ? 'library' : 'legacy',
        ]];
    }

    private function resolvePublicAssetUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        return Storage::url($path);
    }

    /**
     * @param  array<int, string>  $paths
     * @return array<int, string>
     */
    private function resolvePublicAssetUrls(array $paths): array
    {
        return collect($paths)
            ->map(fn (string $path): ?string => $this->resolvePublicAssetUrl($path))
            ->filter()
            ->values()
            ->all();
    }
}
