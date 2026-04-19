<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\TailorProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);

        $validated = $request->validate([
            'status' => ['nullable', 'string'],
            'tailor_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $orders = Order::query()
            ->with(['customer', 'tailor.tailorProfile', 'product.category'])
            ->when(isset($validated['status']), function ($query) use ($validated) {
                $query->where('status', $validated['status']);
            })
            ->when(isset($validated['tailor_id']), function ($query) use ($validated) {
                $query->where('tailor_id', $validated['tailor_id']);
            })
            ->latest()
            ->paginate(30);

        return response()->json(['data' => $orders]);
    }

    public function trackOrder(Request $request, Order $order): JsonResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);

        $order->load([
            'customer',
            'tailor.tailorProfile',
            'product.category',
        ]);

        return response()->json([
            'order' => [
                'id' => $order->id,
                'status' => $order->status,
                'measurements' => $order->measurements,
                'delivery_location' => [
                    'latitude' => $order->delivery_latitude,
                    'longitude' => $order->delivery_longitude,
                ],
                'timestamps' => [
                    'created_at' => optional($order->created_at)?->toISOString(),
                    'accepted_at' => optional($order->accepted_at)?->toISOString(),
                    'last_status_update_at' => optional($order->updated_at)?->toISOString(),
                ],
                'customer' => $order->customer,
                'tailor' => $order->tailor,
                'tailor_profile' => $order->tailor?->tailorProfile,
                'product' => $order->product,
                'category' => $order->product?->category,
            ],
        ]);
    }

    public function statistics(Request $request): JsonResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);

        $todayOrders = Order::query()
            ->whereDate('created_at', now()->toDateString())
            ->count();

        $onlineTailors = TailorProfile::query()
            ->where('status', 'online')
            ->count();

        $topCategories = Order::query()
            ->join('products', 'products.id', '=', 'orders.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->select('categories.id', 'categories.name', DB::raw('COUNT(orders.id) as orders_count'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('orders_count')
            ->limit(5)
            ->get();

        return response()->json([
            'today_orders_total' => $todayOrders,
            'online_tailors_total' => $onlineTailors,
            'top_categories' => $topCategories,
        ]);
    }
}
