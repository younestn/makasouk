<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\TailorProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('adminAccess', Order::class);

        $validated = $request->validate([
            'status' => ['nullable', 'string'],
            'tailor_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $orders = Order::query()
            ->with(['customer', 'tailor.tailorProfile', 'product.category'])
            ->when(isset($validated['status']), fn ($q) => $q->where('status', $validated['status']))
            ->when(isset($validated['tailor_id']), fn ($q) => $q->where('tailor_id', $validated['tailor_id']))
            ->latest()
            ->paginate(30);

        return response()->json(OrderResource::collection($orders));
    }

    public function trackOrder(Request $request, Order $order): JsonResponse
    {
        $this->authorize('adminAccess', Order::class);

        $order->load(['customer', 'tailor.tailorProfile', 'product.category']);

        return response()->json([
            'order' => new OrderResource($order),
            'customer' => $order->customer,
            'tailor' => $order->tailor,
            'tailor_profile' => $order->tailor?->tailorProfile,
            'timestamps' => [
                'created_at' => optional($order->created_at)?->toISOString(),
                'accepted_at' => optional($order->accepted_at)?->toISOString(),
                'last_status_update_at' => optional($order->updated_at)?->toISOString(),
            ],
        ]);
    }

    public function statistics(): JsonResponse
    {
        $this->authorize('adminAccess', Order::class);

        return response()->json([
            'today_orders_total' => Order::query()->whereDate('created_at', now()->toDateString())->count(),
            'online_tailors_total' => TailorProfile::query()->where('status', 'online')->count(),
            'top_categories' => Order::query()
                ->join('products', 'products.id', '=', 'orders.product_id')
                ->join('categories', 'categories.id', '=', 'products.category_id')
                ->select('categories.id', 'categories.name', DB::raw('COUNT(orders.id) as orders_count'))
                ->groupBy('categories.id', 'categories.name')
                ->orderByDesc('orders_count')
                ->limit(5)
                ->get(),
        ]);
    }
}
