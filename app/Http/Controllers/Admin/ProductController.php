<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('manage', User::class);

        $products = Product::query()
            ->with(['category', 'createdByAdmin', 'fabric'])
            ->latest()
            ->paginate(20);

        return response()->json(ProductResource::collection($products));
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('manage', User::class);

        $validated = $request->validated();
        $measurementIds = $validated['measurement_ids'] ?? [];

        $product = Product::query()->create([
            ...collect($validated)->except('measurement_ids')->all(),
            'created_by_admin_id' => $request->user()->id,
        ]);

        if (is_array($measurementIds) && $measurementIds !== []) {
            $product->measurements()->sync($measurementIds);
        }

        $product->load([
            'category',
            'fabric',
            'createdByAdmin',
            'measurements' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('name'),
        ]);

        return response()->json([
            'message' => __('messages.admin.product_created_success'),
            'data' => new ProductResource($product),
        ], 201);
    }
}
