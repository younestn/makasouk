<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Order;
use App\Models\Review;
use App\Models\TailorProfile;
use App\Support\OrderTracking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $reviews = Review::query()
            ->where('customer_id', $request->user()->id)
            ->with(['tailor', 'order.product'])
            ->latest()
            ->paginate(12);

        return ReviewResource::collection($reviews)->additional([
            'meta' => [
                'scope' => 'customer_reviews',
            ],
        ]);
    }

    public function store(StoreReviewRequest $request, Order $order): JsonResponse
    {
        $this->authorize('create', [Review::class, $order]);

        if (! OrderTracking::canReviewOrder($order)) {
            return response()->json(['message' => __('messages.reviews.order_not_completed')], 422);
        }

        if ($order->review()->exists()) {
            return response()->json(['message' => __('messages.reviews.already_exists')], 409);
        }

        $review = DB::transaction(function () use ($order, $request): Review {
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

        $review->loadMissing(['customer', 'tailor']);

        return response()->json([
            'message' => __('messages.reviews.submitted_success'),
            'data' => new ReviewResource($review),
        ], 201);
    }
}
