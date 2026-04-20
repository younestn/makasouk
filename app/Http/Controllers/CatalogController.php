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
            ->with('category')
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->when(isset($validated['category_id']), fn ($query) => $query->where('category_id', $validated['category_id']))
            ->when(
                filled($validated['q'] ?? null),
                fn ($query) => $query->where(function ($searchQuery) use ($validated): void {
                    $searchQuery
                        ->where('name', 'ILIKE', '%'.$validated['q'].'%')
                        ->orWhere('description', 'ILIKE', '%'.$validated['q'].'%');
                })
            )
            ->orderBy('name')
            ->paginate($validated['per_page'] ?? 20);

        return ProductResource::collection($products);
    }

    public function showProduct(Product $product)
    {
        $product->loadMissing('category');

        if (! $product->is_active || ! $product->category?->is_active) {
            abort(404);
        }

        $product->load(['category', 'createdByAdmin']);

        return response()->json([
            'data' => new ProductResource($product),
        ]);
    }
}
