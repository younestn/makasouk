<?php

namespace App\Http\Controllers\Tailor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\TailorProfile;
use App\Support\OrderLifecycle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $tailorId = (int) $request->user()->id;

        $profile = TailorProfile::query()
            ->with('category')
            ->firstOrCreate(['user_id' => $tailorId], ['status' => TailorProfile::STATUS_OFFLINE]);

        $activeOrdersCount = Order::query()
            ->where('tailor_id', $tailorId)
            ->whereIn('status', OrderLifecycle::tailorActiveStatuses())
            ->count();

        return response()->json([
            'data' => [
                'user_id' => $profile->user_id,
                'status' => $profile->status,
                'category_id' => $profile->category_id,
                'category_name' => $profile->category?->name,
                'average_rating' => (float) $profile->average_rating,
                'total_reviews' => (int) $profile->total_reviews,
                'latitude' => $profile->latitude,
                'longitude' => $profile->longitude,
                'active_orders_count' => $activeOrdersCount,
            ],
        ]);
    }

    public function availability(Request $request): JsonResponse
    {
        $tailorId = (int) $request->user()->id;

        $profile = TailorProfile::query()
            ->firstOrCreate(['user_id' => $tailorId], ['status' => TailorProfile::STATUS_OFFLINE]);

        $activeOrdersCount = Order::query()
            ->where('tailor_id', $tailorId)
            ->whereIn('status', OrderLifecycle::tailorActiveStatuses())
            ->count();

        return response()->json([
            'data' => [
                'status' => $profile->status,
                'active_orders_count' => $activeOrdersCount,
            ],
        ]);
    }

    public function toggleAvailability(Request $request): JsonResponse
    {
        $tailorId = (int) $request->user()->id;

        $profile = TailorProfile::query()->firstOrCreate(['user_id' => $tailorId], ['status' => TailorProfile::STATUS_OFFLINE]);
        $nextStatus = $profile->status === TailorProfile::STATUS_ONLINE ? TailorProfile::STATUS_OFFLINE : TailorProfile::STATUS_ONLINE;

        if ($nextStatus === TailorProfile::STATUS_ONLINE) {
            $activeOrdersCount = Order::query()
                ->where('tailor_id', $tailorId)
                ->whereIn('status', OrderLifecycle::tailorActiveStatuses())
                ->count();

            if ($activeOrdersCount >= 5) {
                return response()->json([
                    'message' => 'Too many active orders to switch online right now.',
                    'data' => [
                        'status' => $profile->status,
                        'active_orders_count' => $activeOrdersCount,
                    ],
                ], 422);
            }
        }

        $profile->update(['status' => $nextStatus]);

        return response()->json([
            'message' => 'Availability updated successfully.',
            'data' => [
                'status' => $profile->status,
            ],
        ]);
    }
}