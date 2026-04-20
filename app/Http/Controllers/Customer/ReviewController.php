<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Order;
use App\Models\Review;
use App\Models\TailorProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Order $order): JsonResponse
    {
        $this->authorize('create', [Review::class, $order]);

        if ($order->status !== 'completed') {
            return response()->json(['message' => 'لا يمكن تقييم الطلب قبل اكتماله.'], 422);
        }

        if ($order->review()->exists()) {
            return response()->json(['message' => 'تم تقييم هذا الطلب مسبقاً.'], 409);
        }

        $review = DB::transaction(function () use ($order, $request) {
            $data = $request->validated();

            $createdReview = Review::query()->create([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'tailor_id' => $order->tailor_id,
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
            ]);

            $profile = TailorProfile::query()
                ->where('user_id', $order->tailor_id)
                ->lockForUpdate()
                ->first();

            if ($profile) {
                $newTotal = (int) $profile->total_reviews + 1;
                $newAverage = (((float) $profile->average_rating * (int) $profile->total_reviews) + (int) $data['rating']) / $newTotal;

                $profile->update([
                    'total_reviews' => $newTotal,
                    'average_rating' => round($newAverage, 2),
                ]);
            }

            return $createdReview;
        });

        return response()->json([
            'message' => 'تم حفظ التقييم بنجاح',
            'data' => new ReviewResource($review),
        ], 201);
    }
}
