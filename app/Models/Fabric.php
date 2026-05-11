<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Fabric extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_en',
        'name_ar',
        'slug',
        'country',
        'description',
        'description_en',
        'description_ar',
        'image_path',
        'reference_code',
        'notes',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function localizedName(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        if ($locale === 'ar' && filled($this->name_ar)) {
            return (string) $this->name_ar;
        }

        return (string) ($this->name_en ?: $this->name ?: $this->name_ar ?: '');
    }

    public function localizedDescription(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        if ($locale === 'ar' && filled($this->description_ar)) {
            return $this->description_ar;
        }

        return $this->description_en ?: $this->description ?: $this->description_ar;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->localizedName();
    }

    public function getDisplayDescriptionAttribute(): ?string
    {
        return $this->localizedDescription();
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! filled($this->image_path)) {
            return null;
        }

        if (Str::startsWith($this->image_path, ['http://', 'https://', '/'])) {
            return $this->image_path;
        }

        return Storage::url($this->image_path);
    }
}
