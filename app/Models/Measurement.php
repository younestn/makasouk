<?php

namespace App\Models;

use App\Support\Tailor\MeasurementOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Measurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'audience',
        'description',
        'guide_text',
        'helper_text',
        'guide_image_path',
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

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeForAudience(Builder $query, ?string $audience): Builder
    {
        if (! filled($audience)) {
            return $query;
        }

        return $query->whereIn('audience', [$audience, MeasurementOptions::AUDIENCE_UNISEX]);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'measurement_product')
            ->withTimestamps();
    }
}
