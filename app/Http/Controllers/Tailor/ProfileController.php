<?php

namespace App\Http\Controllers\Tailor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tailor\UpdateLocationRequest;
use App\Models\Order;
use App\Models\TailorProfile;
use App\Support\OrderLifecycle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $tailorId = (int) $request->user()->id;

        $profile = TailorProfile::query()
            ->with('category')
            ->firstOrCreate(['user_id' => $tailorId], ['status' => TailorProfile::STATUS_OFFLINE]);

        return response()->json(['data' => $this->profilePayload($profile)]);
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
                    'message' => __('messages.tailor.too_many_active_to_go_online'),
                    'data' => [
                        'status' => $profile->status,
                        'active_orders_count' => $activeOrdersCount,
                    ],
                ], 422);
            }
        }

        $profile->update(['status' => $nextStatus]);

        return response()->json([
            'message' => __('messages.tailor.availability_updated_success'),
            'data' => [
                'status' => $profile->status,
            ],
        ]);
    }

    public function updateLocation(UpdateLocationRequest $request): JsonResponse
    {
        $tailorId = (int) $request->user()->id;
        $data = $request->validated();

        $profile = TailorProfile::query()->firstOrCreate(
            ['user_id' => $tailorId],
            ['status' => TailorProfile::STATUS_OFFLINE],
        );

        $latitude = array_key_exists('latitude', $data) ? $data['latitude'] : $profile->latitude;
        $longitude = array_key_exists('longitude', $data) ? $data['longitude'] : $profile->longitude;

        $profile->forceFill([
            'work_wilaya' => $data['work_wilaya'] ?? $profile->work_wilaya,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ])->save();

        if ($latitude !== null && $longitude !== null) {
            DB::statement(
                'UPDATE tailor_profiles SET location = ST_SetSRID(ST_MakePoint(?, ?), 4326) WHERE id = ?',
                [(float) $longitude, (float) $latitude, $profile->id],
            );
        } else {
            DB::statement(
                'UPDATE tailor_profiles SET location = NULL WHERE id = ?',
                [$profile->id],
            );
        }

        $profile->refresh()->load('category');

        return response()->json([
            'message' => __('messages.tailor.location_updated_success'),
            'data' => $this->profilePayload($profile),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function profilePayload(TailorProfile $profile): array
    {
        $activeOrdersCount = Order::query()
            ->where('tailor_id', $profile->user_id)
            ->whereIn('status', OrderLifecycle::tailorActiveStatuses())
            ->count();

        return [
            'user_id' => $profile->user_id,
            'status' => $profile->status,
            'category_id' => $profile->category_id,
            'category_name' => $profile->category?->name,
            'average_rating' => (float) $profile->average_rating,
            'total_reviews' => (int) $profile->total_reviews,
            'score' => (int) ($profile->score ?? 100),
            'latitude' => $profile->latitude,
            'longitude' => $profile->longitude,
            'specialization' => $profile->specialization,
            'work_wilaya' => $profile->work_wilaya,
            'years_of_experience' => $profile->years_of_experience,
            'gender' => $profile->gender,
            'workers_count' => $profile->workers_count,
            'commercial_register_url' => filled($profile->commercial_register_path)
                ? Storage::disk('public')->url($profile->commercial_register_path)
                : null,
            'active_orders_count' => $activeOrdersCount,
        ];
    }
}
