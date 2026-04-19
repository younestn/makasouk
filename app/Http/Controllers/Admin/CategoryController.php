<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);

        return response()->json([
            'data' => Category::query()->latest()->paginate(20),
        ]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::query()->create($request->validated());

        return response()->json([
            'message' => 'تم إنشاء التصنيف بنجاح',
            'data' => $category,
        ], 201);
    }
}
