<?php

namespace App\Http\Controllers\Customer;

use App\Events\OrderCancelled;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CancelOrderRequest;
use App\Http\Requests\Customer\StoreOrderRequest;
use App\Http\Resources\OrderResource;
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
        $order = DB::transaction(function () use ($request) {
            $data = $request->validated();

            return Order::query()->create([
                'customer_id' => $request->user()->id,
                'product_id' => $data['product_id'],
                'measurements' => $data['measurements'],
                'delivery_latitude' => $data['customer_location']['latitude'],
                'delivery_longitude' => $data['customer_location']['longitude'],
                'delivery_location' => DB::raw(sprintf(
                    'ST_SetSRID(ST_MakePoint(%F, %F), 4326)',
                    (float) $data['customer_location']['longitude'],
                    (float) $data['customer_location']['latitude'],
                )),
                'status' => Order::STATUS_SEARCHING_FOR_TAILOR,
            ]);
        });

        $order->load('product.category');
        $tailors = $this->orderMatchingService->findNearbyTailors($order, 20);

        if ($tailors->isEmpty()) {
            $order->update(['status' => Order::STATUS_NO_TAILORS_AVAILABLE]);

            return response()->json([
                'message' => 'لم يتم العثور على خياطين متاحين حالياً',
                'status' => Order::STATUS_NO_TAILORS_AVAILABLE,
                'order' => new OrderResource($order->fresh(['product.category'])),
            ], 201);
        }

        $this->orderMatchingService->broadcastOrderToTailors($order, $tailors);

        return response()->json([
            'message' => 'تم إنشاء الطلب وجارٍ البحث عن خياط مناسب',
            'status' => Order::STATUS_SEARCHING_FOR_TAILOR,
            'matched_tailors_count' => $tailors->count(),
            'order' => new OrderResource($order),
        ], 201);
    }

    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order->load(['product.category', 'tailor.tailorProfile.category', 'review']);

        return response()->json([
            'order' => new OrderResource($order),
            'status' => $order->status,
            'is_accepted' => $order->tailor_id !== null,
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->where('customer_id', $request->user()->id)
            ->whereIn('status', [
                Order::STATUS_COMPLETED,
                Order::STATUS_CANCELLED,
                Order::STATUS_CANCELLED_BY_CUSTOMER,
                Order::STATUS_CANCELLED_BY_TAILOR,
                Order::STATUS_NO_TAILORS_AVAILABLE,
            ])
            ->with(['tailor.tailorProfile', 'product.category', 'review'])
            ->latest('updated_at')
            ->paginate(20);

        return response()->json(OrderResource::collection($orders));
    }

    public function cancel(CancelOrderRequest $request, Order $order): JsonResponse
    {
        $this->authorize('cancelByCustomer', $order);

        if (! in_array($order->status, [Order::STATUS_SEARCHING_FOR_TAILOR, Order::STATUS_ACCEPTED, Order::STATUS_PROCESSING], true)) {
            return response()->json(['message' => 'لا يمكن إلغاء هذا الطلب في حالته الحالية.', 'status' => $order->status], 422);
        }

        $warning = $order->status === Order::STATUS_PROCESSING
            ? 'تم إلغاء الطلب أثناء التنفيذ وقد تطبق غرامة حسب سياسة المنصة.'
            : null;

        $order->update([
            'status' => Order::STATUS_CANCELLED_BY_CUSTOMER,
            'cancellation_reason' => $request->validated('reason'),
        ]);

        $order->refresh();

        if ($order->tailor_id !== null) {
            Event::dispatch(new OrderCancelled($order));
        }

        return response()->json([
            'message' => 'تم إلغاء الطلب بنجاح',
            'warning' => $warning,
            'order' => new OrderResource($order),
        ]);
    }
}
