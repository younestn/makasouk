<?php

namespace App\Services;

use App\Events\OrderCreated;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Event;

class OrderMatchingService
{
    /**
     * @return Collection<int, User>
     */
    public function findNearbyTailors(Order $order, float $radiusInKm = 20): Collection
    {
        $order->loadMissing(['product.category']);

        $categoryId = (int) $order->product->category_id;
        $radiusInMeters = $radiusInKm * 1000;

        return User::query()
            ->select('users.*')
            ->selectRaw(
                'ST_Distance(tp.location::geography, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography) / 1000 as distance_km',
                [$order->delivery_longitude, $order->delivery_latitude],
            )
            ->join('tailor_profiles as tp', 'tp.user_id', '=', 'users.id')
            ->where('users.role', 'tailor')
            ->where('tp.status', 'online')
            ->where('tp.category_id', $categoryId)
            ->whereNotNull('tp.location')
            ->whereRaw(
                'ST_DWithin(tp.location::geography, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography, ?)',
                [$order->delivery_longitude, $order->delivery_latitude, $radiusInMeters],
            )
            ->orderBy('distance_km')
            ->get();
    }

    /**
     * @param Collection<int, User> $tailors
     */
    public function broadcastOrderToTailors(Order $order, Collection $tailors): void
    {
        if ($tailors->isEmpty()) {
            return;
        }

        $tailorIds = $tailors->pluck('id')->map(static fn ($id) => (int) $id)->values()->all();
        $distancesByTailorId = $tailors
            ->pluck('distance_km', 'id')
            ->mapWithKeys(static fn ($distance, $id) => [(int) $id => round((float) $distance, 2)])
            ->all();

        Event::dispatch(new OrderCreated($order, $tailorIds, $distancesByTailorId));
    }
}
