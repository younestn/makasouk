<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use App\Models\TailorProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function store(Request $request, Order $order): JsonResponse
    {
        abort_unless($request->user()?->role === 'customer', 403);
        abort_unless((int) $order->customer_id === (int) $request->user()->id, 403);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($order->status !== 'completed') {
            return response()->json([
                'message' => 'لا يمكن تقييم الطلب قبل اكتماله.',
            ], 422);
        }

        if ($order->review()->exists()) {
            return response()->json([
                'message' => 'تم تقييم هذا الطلب مسبقاً.',
            ], 409);
        }

        $review = DB::transaction(function () use ($order, $validated) {
            $createdReview = Review::query()->create([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'tailor_id' => $order->tailor_id,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]);

            $profile = TailorProfile::query()
                ->where('user_id', $order->tailor_id)
                ->lockForUpdate()
                ->first();

            if ($profile) {
                $currentTotal = (int) $profile->total_reviews;
                $currentAverage = (float) $profile->average_rating;
                $newTotal = $currentTotal + 1;
                $newAverage = (($currentAverage * $currentTotal) + (int) $validated['rating']) / $newTotal;

                $profile->update([
                    'total_reviews' => $newTotal,
                    'average_rating' => round($newAverage, 2),
                ]);
            }

            return $createdReview;
        });

        return response()->json([
            'message' => 'تم حفظ التقييم بنجاح',
            'data' => $review,
        ], 201);
    }
}
