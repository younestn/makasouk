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
        'name_en',
        'name_ar',
        'slug',
        'audience',
        'audiences',
        'description',
        'description_en',
        'description_ar',
        'guide_text',
        'guide_text_en',
        'guide_text_ar',
        'helper_text',
        'helper_text_en',
        'helper_text_ar',
        'guide_image_path',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'audiences' => 'array',
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

        return $query->where(function (Builder $audienceQuery) use ($audience): void {
            $audienceQuery
                ->whereJsonContains('audiences', $audience)
                ->orWhereIn('audience', [$audience, MeasurementOptions::AUDIENCE_UNISEX]);
        });
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'measurement_product')
            ->withTimestamps();
    }

    /**
     * @return array<int, string>
     */
    public function normalizedAudiences(): array
    {
        return MeasurementOptions::normalizeAudiences($this->audiences, $this->audience);
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

    public function localizedGuideText(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        if ($locale === 'ar' && filled($this->guide_text_ar)) {
            return $this->guide_text_ar;
        }

        return $this->guide_text_en ?: $this->guide_text ?: $this->guide_text_ar;
    }

    public function localizedHelperText(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        if ($locale === 'ar' && filled($this->helper_text_ar)) {
            return $this->helper_text_ar;
        }

        return $this->helper_text_en ?: $this->helper_text ?: $this->helper_text_ar;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->localizedName();
    }

    public function getDisplayDescriptionAttribute(): ?string
    {
        return $this->localizedDescription();
    }

    public function getDisplayGuideTextAttribute(): ?string
    {
        return $this->localizedGuideText();
    }

    public function getDisplayHelperTextAttribute(): ?string
    {
        return $this->localizedHelperText();
    }

    public function getDisplayAudienceLabelsAttribute(): string
    {
        return MeasurementOptions::formatAudienceLabels($this->normalizedAudiences());
    }
}
