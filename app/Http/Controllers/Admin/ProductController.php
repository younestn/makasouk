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

        $products = Product::query()->with(['category', 'createdByAdmin'])->latest()->paginate(20);
        return response()->json(ProductResource::collection($products));
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('manage', User::class);

        $product = Product::query()->create([
            ...$request->validated(),
            'created_by_admin_id' => $request->user()->id,
        ]);

        $product->load(['category', 'createdByAdmin']);

        return response()->json(['message' => 'تم إنشاء الموديل بنجاح', 'data' => new ProductResource($product)], 201);
    }
}
