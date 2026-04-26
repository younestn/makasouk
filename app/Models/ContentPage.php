<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title_en',
        'title_ar',
        'excerpt_en',
        'excerpt_ar',
        'body_en',
        'body_ar',
        'placement',
        'show_in_footer',
        'is_published',
        'sort_order',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'show_in_footer' => 'boolean',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->where(function (Builder $published): void {
                $published->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function scopeFooter(Builder $query): Builder
    {
        return $query
            ->where('placement', 'footer')
            ->where('show_in_footer', true);
    }

    public function localizedTitle(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return (string) ($locale === 'ar' && filled($this->title_ar) ? $this->title_ar : $this->title_en);
    }

    public function localizedExcerpt(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        return $locale === 'ar' && filled($this->excerpt_ar) ? $this->excerpt_ar : $this->excerpt_en;
    }

    public function localizedBody(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        return $locale === 'ar' && filled($this->body_ar) ? $this->body_ar : $this->body_en;
    }
}
