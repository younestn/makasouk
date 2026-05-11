<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function categories(Request $request)
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($validated['per_page'] ?? 50);

        return CategoryResource::collection($categories);
    }

    public function products(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'q' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $products = Product::query()
            ->with(['category', 'fabric'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->when(isset($validated['category_id']), fn ($query) => $query->where('category_id', $validated['category_id']))
            ->when(
                filled($validated['q'] ?? null),
                fn ($query) => $query->where(function ($searchQuery) use ($validated): void {
                    $searchQuery
                        ->where('name', 'like', '%'.$validated['q'].'%')
                        ->orWhere('short_description', 'like', '%'.$validated['q'].'%')
                        ->orWhere('description', 'like', '%'.$validated['q'].'%');
                })
            )
            ->orderBy('name')
            ->paginate($validated['per_page'] ?? 20);

        return ProductResource::collection($products);
    }

    public function showProduct(Product $product)
    {
        $product->loadMissing(['category', 'fabric']);

        if (! $product->is_active || ! $product->category?->is_active) {
            abort(404);
        }

        $product->load([
            'category',
            'fabric',
            'createdByAdmin',
            'measurements' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('name'),
        ]);

        $product->loadCount('reviews');
        $product->loadAvg('reviews', 'rating');

        $similarProducts = Product::query()
            ->with(['category', 'fabric'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->published()
            ->whereKeyNot($product->id)
            ->where('category_id', $product->category_id)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->orderByDesc('is_best_seller')
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->limit(4)
            ->get();

        return response()->json([
            'data' => new ProductResource($product),
            'meta' => [
                'similar_products' => ProductResource::collection($similarProducts)->resolve(),
            ],
        ]);
    }
}
