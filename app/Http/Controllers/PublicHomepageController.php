<?php

namespace App\Http\Controllers;

use App\Models\ContentPage;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\ShopBanner;
use App\Models\ShopSetting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicHomepageController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $settings = ShopSetting::current();
        $locale = app()->getLocale();

        return response()->json([
            'data' => [
                'settings' => [
                    'hero_enabled' => (bool) $settings->homepage_hero_enabled,
                    'stats_enabled' => (bool) $settings->homepage_stats_enabled,
                    'best_sellers_enabled' => (bool) $settings->homepage_best_sellers_enabled,
                    'testimonials_enabled' => (bool) $settings->homepage_testimonials_enabled,
                    'section_order' => $settings->homepage_section_order ?: [
                        'hero',
                        'best_sellers',
                        'stats',
                        'testimonials',
                        'trust',
                    ],
                ],
                'hero' => $this->hero($settings, $locale),
                'stats' => $this->stats(),
                'best_sellers' => $this->bestSellers($settings),
                'testimonials' => $this->testimonials(),
                'footer_pages' => $this->getFooterPages($locale),
            ],
        ]);
    }

    public function footerPageLinks(): JsonResponse
    {
        return response()->json([
            'data' => $this->getFooterPages(app()->getLocale()),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function hero(ShopSetting $settings, string $locale): array
    {
        $banner = ShopBanner::query()
            ->forPlacement('home_hero')
            ->active()
            ->currentlyPublished()
            ->orderBy('display_order')
            ->orderByDesc('created_at')
            ->first();

        return [
            'badge' => $this->localized($settings, 'homepage_badge', $locale) ?: $banner?->badge,
            'title' => $this->localized($settings, 'homepage_title', $locale) ?: $banner?->title,
            'subtitle' => $this->localized($settings, 'homepage_subtitle', $locale) ?: $banner?->subtitle,
            'primary_cta_label' => $this->localized($settings, 'homepage_primary_cta_label', $locale),
            'primary_cta_url' => $settings->homepage_primary_cta_url ?: '/shop',
            'secondary_cta_label' => $this->localized($settings, 'homepage_secondary_cta_label', $locale),
            'secondary_cta_url' => $settings->homepage_secondary_cta_url ?: '/how-it-works',
            'image_url' => $banner?->image_path ? $this->assetUrl($banner->image_path) : null,
        ];
    }

    /**
     * @return array<string, int|float>
     */
    private function stats(): array
    {
        return [
            'sales_count' => Order::query()->where('status', Order::STATUS_COMPLETED)->count(),
            'orders_count' => Order::query()->count(),
            'tailors_count' => User::query()
                ->where('role', User::ROLE_TAILOR)
                ->whereNotNull('approved_at')
                ->count(),
            'customers_count' => User::query()->where('role', User::ROLE_CUSTOMER)->count(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function bestSellers(ShopSetting $settings): array
    {
        $limit = max(1, min((int) $settings->products_per_section, 8));

        return Product::query()
            ->published()
            ->whereHas('category', fn ($query) => $query->active())
            ->with(['category:id,name,slug', 'fabric'])
            ->withCount(['orders as completed_orders_count' => fn ($query) => $query->where('status', Order::STATUS_COMPLETED)])
            ->orderByDesc('completed_orders_count')
            ->orderByDesc('is_best_seller')
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get()
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'category' => $product->category?->name,
                'price' => (float) ($product->sale_price ?? $product->price),
                'image_url' => $product->main_image_url,
                'fabric_type' => $product->display_fabric_type,
                'completed_orders_count' => (int) $product->completed_orders_count,
                'url' => route('shop.product.show', $product),
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function testimonials(): array
    {
        return Review::query()
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->with('customer:id,name')
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn (Review $review): array => [
                'id' => $review->id,
                'name' => $review->customer?->name ?: __('shop.home.testimonial_customer'),
                'rating' => (int) $review->rating,
                'comment' => Str::limit((string) $review->comment, 220),
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    private function getFooterPages(string $locale): array
    {
        return ContentPage::query()
            ->published()
            ->footer()
            ->orderBy('sort_order')
            ->orderBy('title_en')
            ->get()
            ->map(fn (ContentPage $page): array => [
                'title' => $page->localizedTitle($locale),
                'excerpt' => $page->localizedExcerpt($locale),
                'url' => route('content-pages.show', $page),
            ])
            ->all();
    }

    private function localized(ShopSetting $settings, string $key, string $locale): ?string
    {
        $localized = $settings->getAttribute($key.'_'.$locale);

        if (filled($localized)) {
            return (string) $localized;
        }

        return $settings->getAttribute($key.'_en');
    }

    private function assetUrl(string $path): string
    {
        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        return Storage::url($path);
    }
}
