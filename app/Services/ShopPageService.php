<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShopBanner;
use App\Models\ShopSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ShopPageService
{
    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function buildShopPageData(array $filters = []): array
    {
        $settings = ShopSetting::current();

        $banners = ShopBanner::query()
            ->forPlacement('shop_hero')
            ->active()
            ->currentlyPublished()
            ->orderBy('display_order')
            ->orderByDesc('created_at')
            ->get();

        $categories = Category::query()
            ->active()
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->withCount(['products' => fn (Builder $query) => $query->published()])
            ->get();

        $newArrivals = Product::query()
            ->published()
            ->whereHas('category', fn (Builder $query) => $query->active())
            ->with(['category:id,name,slug', 'fabric'])
            ->newArrivals()
            ->limit($settings->products_per_section)
            ->get();

        $bestSellerIds = $this->bestSellerIds($settings->products_per_section);

        $bestSellers = Product::query()
            ->published()
            ->whereHas('category', fn (Builder $query) => $query->active())
            ->with(['category:id,name,slug', 'fabric'])
            ->when($bestSellerIds->isNotEmpty(), function (Builder $query) use ($bestSellerIds): void {
                $query->whereIn('id', $bestSellerIds)->orderByRaw('array_position(ARRAY['.$bestSellerIds->implode(',').']::bigint[], id)');
            }, function (Builder $query): void {
                $query->bestSeller()->latest('published_at')->latest('created_at');
            })
            ->limit($settings->products_per_section)
            ->get();

        $categorySections = Category::query()
            ->active()
            ->featured()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit($settings->featured_categories_limit)
            ->with(['products' => function ($query) use ($settings): void {
                $query->published()
                    ->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->active())
                    ->with('fabric')
                    ->orderByDesc('is_featured')
                    ->orderByDesc('published_at')
                    ->orderByDesc('created_at')
                    ->limit($settings->products_per_section);
            }])
            ->get();

        $allProducts = $this->buildAllProductsQuery($filters)
            ->with(['category:id,name,slug', 'fabric'])
            ->paginate($settings->all_products_per_page)
            ->withQueryString();

        return [
            'settings' => $settings,
            'filters' => $filters,
            'banners' => $banners,
            'categories' => $categories,
            'newArrivals' => $newArrivals,
            'bestSellers' => $bestSellers,
            'categorySections' => $categorySections,
            'allProducts' => $allProducts,
        ];
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function buildAllProductsQuery(array $filters): Builder
    {
        $query = Product::query()
            ->published()
            ->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->active())
            ->when($filters['category'] ?? null, fn (Builder $productsQuery, string $categorySlug) => $productsQuery->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('slug', $categorySlug)))
            ->when($filters['featured'] ?? null, fn (Builder $productsQuery) => $productsQuery->featured())
            ->when($filters['best_seller'] ?? null, fn (Builder $productsQuery) => $productsQuery->bestSeller())
            ->when($filters['in_stock'] ?? null, fn (Builder $productsQuery) => $productsQuery->inStock())
            ->when($filters['q'] ?? null, function (Builder $productsQuery, string $search): void {
                $productsQuery->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery->where('name', 'ilike', "%{$search}%")
                        ->orWhere('short_description', 'ilike', "%{$search}%")
                        ->orWhere('description', 'ilike', "%{$search}%")
                        ->orWhere('sku', 'ilike', "%{$search}%");
                });
            })
            ->when($filters['min_price'] ?? null, fn (Builder $productsQuery, mixed $price) => $productsQuery->where('price', '>=', (float) $price))
            ->when($filters['max_price'] ?? null, fn (Builder $productsQuery, mixed $price) => $productsQuery->where('price', '<=', (float) $price));

        $sort = $filters['sort'] ?? 'newest';

        return match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'best_selling' => $query->orderByDesc('is_best_seller')->orderByDesc('published_at')->orderByDesc('created_at'),
            default => $query->orderByDesc('published_at')->orderByDesc('created_at'),
        };
    }

    /**
     * @return Collection<int, int>
     */
    private function bestSellerIds(int $limit): Collection
    {
        return Order::query()
            ->where('status', Order::STATUS_COMPLETED)
            ->selectRaw('product_id, COUNT(*) as total_sales')
            ->groupBy('product_id')
            ->orderByDesc('total_sales')
            ->limit($limit)
            ->pluck('product_id');
    }
}
