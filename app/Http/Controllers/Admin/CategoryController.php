<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('manage', User::class);

        return response()->json(CategoryResource::collection(Category::query()->latest()->paginate(20)));
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $this->authorize('manage', User::class);
        $category = Category::query()->create($request->validated());

        return response()->json([
            'message' => __('messages.admin.category_created_success'),
            'data' => new CategoryResource($category),
        ], 201);
    }
}

