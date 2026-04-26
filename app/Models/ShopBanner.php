<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopBanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'badge',
        'image_path',
        'button_text',
        'button_link',
        'placement',
        'display_order',
        'is_active',
        'publish_starts_at',
        'publish_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'display_order' => 'integer',
            'is_active' => 'boolean',
            'publish_starts_at' => 'datetime',
            'publish_ends_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForPlacement(Builder $query, string $placement): Builder
    {
        return $query->where('placement', $placement);
    }

    public function scopeCurrentlyPublished(Builder $query): Builder
    {
        return $query->where(function (Builder $starts): void {
            $starts->whereNull('publish_starts_at')->orWhere('publish_starts_at', '<=', now());
        })->where(function (Builder $ends): void {
            $ends->whereNull('publish_ends_at')->orWhere('publish_ends_at', '>=', now());
        });
    }
}
