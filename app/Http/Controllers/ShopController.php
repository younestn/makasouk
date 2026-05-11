<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\ShopPageService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function __construct(private readonly ShopPageService $shopPageService)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->only([
            'category',
            'sort',
            'min_price',
            'max_price',
            'featured',
            'best_seller',
            'in_stock',
            'q',
        ]);

        return view('shop.index', $this->shopPageService->buildShopPageData($filters));
    }

    public function category(Category $category, Request $request): View
    {
        $filters = array_merge($request->only([
            'sort',
            'min_price',
            'max_price',
            'featured',
            'best_seller',
            'in_stock',
            'q',
        ]), [
            'category' => $category->slug,
        ]);

        return view('shop.index', $this->shopPageService->buildShopPageData($filters));
    }

    public function showProduct(Product $product): View
    {
        abort_unless($product->is_active && $product->category?->is_active, 404);

        $product->loadMissing(['category:id,name,slug', 'fabric']);
        $product->loadCount('reviews');
        $product->loadAvg('reviews', 'rating');

        $similarProducts = Product::query()
            ->published()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->whereHas('category', fn ($query) => $query->active())
            ->with(['category:id,name,slug', 'fabric'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->orderByDesc('is_best_seller')
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->limit(4)
            ->get();

        return view('shop.product-show', [
            'product' => $product,
            'similarProducts' => $similarProducts,
        ]);
    }
}
