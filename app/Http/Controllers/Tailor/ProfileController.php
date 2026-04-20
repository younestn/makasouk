<?php

namespace App\Http\Controllers\Tailor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\TailorProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function toggleAvailability(Request $request): JsonResponse
    {
        $tailorId = (int) $request->user()->id;

        $profile = TailorProfile::query()->firstOrCreate(['user_id' => $tailorId], ['status' => TailorProfile::STATUS_OFFLINE]);
        $nextStatus = $profile->status === TailorProfile::STATUS_ONLINE ? TailorProfile::STATUS_OFFLINE : TailorProfile::STATUS_ONLINE;

        if ($nextStatus === TailorProfile::STATUS_ONLINE) {
            $activeOrdersCount = Order::query()
                ->where('tailor_id', $tailorId)
                ->whereIn('status', [Order::STATUS_ACCEPTED, Order::STATUS_PROCESSING, Order::STATUS_READY_FOR_DELIVERY])
                ->count();

            if ($activeOrdersCount >= 5) {
                return response()->json([
                    'message' => 'لديك عدد كبير من الطلبات النشطة. يرجى إنهاء بعض الطلبات قبل التحول إلى متاح.',
                    'active_orders_count' => $activeOrdersCount,
                    'status' => $profile->status,
                ], 422);
            }
        }

        $profile->update(['status' => $nextStatus]);

        return response()->json(['message' => 'تم تحديث حالة التوفر بنجاح', 'status' => $profile->status]);
    }
}
