<?php

namespace App\Http\Controllers\Customer;

use App\Events\OrderCancelled;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreOrderRequest;
use App\Models\Order;
use App\Services\OrderMatchingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class OrderController extends Controller
{
    public function __construct(private readonly OrderMatchingService $orderMatchingService)
    {
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $order = DB::transaction(function () use ($validated, $request) {
            return Order::query()->create([
                'customer_id' => $request->user()->id,
                'product_id' => $validated['product_id'],
                'measurements' => $validated['measurements'],
                'delivery_latitude' => $validated['customer_location']['latitude'],
                'delivery_longitude' => $validated['customer_location']['longitude'],
                'delivery_location' => DB::raw(sprintf(
                    'ST_SetSRID(ST_MakePoint(%F, %F), 4326)',
                    (float) $validated['customer_location']['longitude'],
                    (float) $validated['customer_location']['latitude'],
                )),
                'status' => 'searching_for_tailor',
            ]);
        });

        $order->load('product.category');

        $tailors = $this->orderMatchingService->findNearbyTailors($order, 20);

        if ($tailors->isEmpty()) {
            $order->update(['status' => 'no_tailors_available']);

            return response()->json([
                'message' => 'لم يتم العثور على خياطين متاحين حالياً',
                'status' => 'no_tailors_available',
                'order' => $order->fresh(['product.category']),
            ], 201);
        }

        $this->orderMatchingService->broadcastOrderToTailors($order, $tailors);

        return response()->json([
            'message' => 'تم إنشاء الطلب وجارٍ البحث عن خياط مناسب',
            'status' => 'searching_for_tailor',
            'matched_tailors_count' => $tailors->count(),
            'order' => $order,
        ], 201);
    }

    public function show(Order $order): JsonResponse
    {
        abort_unless(auth()->user()?->role === 'customer', 403);
        abort_unless($order->customer_id === auth()->id(), 403);

        $order->load(['product.category', 'tailor.tailorProfile.category', 'review']);

        return response()->json([
            'order' => $order,
            'status' => $order->status,
            'is_accepted' => $order->tailor_id !== null,
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        abort_unless($request->user()?->role === 'customer', 403);

        $orders = Order::query()
            ->where('customer_id', $request->user()->id)
            ->whereIn('status', ['completed', 'cancelled', 'cancelled_by_customer', 'cancelled_by_tailor', 'no_tailors_available'])
            ->with(['tailor.tailorProfile', 'product.category', 'review'])
            ->latest('updated_at')
            ->paginate(20);

        return response()->json(['data' => $orders]);
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        abort_unless($request->user()?->role === 'customer', 403);
        abort_unless((int) $order->customer_id === (int) $request->user()->id, 403);

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $cancellableStatuses = ['searching_for_tailor', 'accepted', 'processing'];

        if (! in_array($order->status, $cancellableStatuses, true)) {
            return response()->json([
                'message' => 'لا يمكن إلغاء هذا الطلب في حالته الحالية.',
                'status' => $order->status,
            ], 422);
        }

        $warning = $order->status === 'processing'
            ? 'تم إلغاء الطلب أثناء التنفيذ وقد تطبق غرامة حسب سياسة المنصة.'
            : null;

        $order->update([
            'status' => 'cancelled_by_customer',
            'cancellation_reason' => $validated['reason'] ?? null,
        ]);

        $order->refresh();

        if ($order->tailor_id !== null) {
            Event::dispatch(new OrderCancelled($order));
        }

        return response()->json([
            'message' => 'تم إلغاء الطلب بنجاح',
            'warning' => $warning,
            'order' => $order,
        ]);
    }
}
