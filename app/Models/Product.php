<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
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
        'main_image',
        'pattern_file_path',
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

    public function measurements(): BelongsToMany
    {
        return $this->belongsToMany(Measurement::class, 'measurement_product')
            ->withTimestamps()
            ->orderBy('measurements.sort_order')
            ->orderBy('measurements.name');
    }

    public function getMainImageUrlAttribute(): ?string
    {
        return $this->resolvePublicAssetUrl($this->main_image);
    }

    public function getFabricImageUrlAttribute(): ?string
    {
        return $this->display_fabric_image_url;
    }

    public function getPatternFileUrlAttribute(): ?string
    {
        return $this->resolvePublicAssetUrl($this->pattern_file_path);
    }

    public function getDisplayFabricTypeAttribute(): ?string
    {
        return $this->fabric?->name ?? $this->fabric_type;
    }

    public function getDisplayFabricCountryAttribute(): ?string
    {
        return $this->fabric?->country ?? $this->fabric_country;
    }

    public function getDisplayFabricDescriptionAttribute(): ?string
    {
        return $this->fabric?->description ?? $this->fabric_description;
    }

    public function getDisplayFabricImageUrlAttribute(): ?string
    {
        return $this->fabric?->image_url ?? $this->resolvePublicAssetUrl($this->fabric_image_path);
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
}
