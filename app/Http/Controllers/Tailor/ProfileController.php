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
        abort_unless($request->user()?->role === 'tailor', 403);

        $tailorId = (int) $request->user()->id;

        $profile = TailorProfile::query()->firstOrCreate(
            ['user_id' => $tailorId],
            ['status' => 'offline'],
        );

        $nextStatus = $profile->status === 'online' ? 'offline' : 'online';

        if ($nextStatus === 'online') {
            $activeOrdersCount = Order::query()
                ->where('tailor_id', $tailorId)
                ->whereIn('status', ['accepted', 'processing', 'ready_for_delivery'])
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

        return response()->json([
            'message' => 'تم تحديث حالة التوفر بنجاح',
            'status' => $profile->status,
        ]);
    }
}
