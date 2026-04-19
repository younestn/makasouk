<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);

        $products = Product::query()
            ->with(['category', 'createdByAdmin'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => $products,
        ]);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::query()->create([
            ...$request->validated(),
            'created_by_admin_id' => $request->user()->id,
        ]);

        $product->load(['category', 'createdByAdmin']);

        return response()->json([
            'message' => 'تم إنشاء الموديل بنجاح',
            'data' => $product,
        ], 201);
    }
}
