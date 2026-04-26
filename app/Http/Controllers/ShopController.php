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

        return view('shop.product-show', [
            'product' => $product->load(['category:id,name,slug', 'fabric']),
        ]);
    }
}
