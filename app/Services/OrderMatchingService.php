<?php

namespace App\Services;

use App\Events\OrderCreated;
use App\Mail\IncomingTailorOrderMail;
use App\Models\MailSetting;
use App\Models\Order;
use App\Models\TailorProfile;
use App\Models\TailorOrderOffer;
use App\Models\User;
use App\Services\Mail\MailConfigurationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

class OrderMatchingService
{
    /**
     * @return Collection<int, User>
     */
    public function findNearbyTailors(Order $order, float $radiusInKm = 20): Collection
    {
        unset($radiusInKm);

        $order->loadMissing(['product.category']);

        $categoryId = (int) ($order->product?->category_id ?? 0);
        $resolvedSpecialization = $this->resolveTargetSpecialization($order);
        $deliveryLatitude = $order->delivery_latitude;
        $deliveryLongitude = $order->delivery_longitude;
        $deliveryWilaya = $order->delivery_work_wilaya;

        $query = User::query()
            ->select('users.*')
            ->addSelect([
                'tp.work_wilaya as tailor_work_wilaya',
                'tp.specialization as tailor_specialization',
                'tp.category_id as tailor_category_id',
            ])
            ->join('tailor_profiles as tp', 'tp.user_id', '=', 'users.id')
            ->where('users.role', User::ROLE_TAILOR)
            ->where('users.is_suspended', false)
            ->whereNotNull('users.approved_at')
            ->where('tp.status', TailorProfile::STATUS_ONLINE);

        if (MailSetting::tailorPhoneVerificationEnabled()) {
            $query->where(function ($builder): void {
                $builder->whereNull('users.phone')->orWhereNotNull('users.phone_verified_at');
            });
        }

        if ($resolvedSpecialization !== null) {
            $query->where('tp.specialization', $resolvedSpecialization);
        } elseif ($categoryId > 0) {
            // Legacy fallback when category specialization has not been configured yet.
            $query->where('tp.category_id', $categoryId);
        }

        if ($deliveryLongitude !== null && $deliveryLatitude !== null) {
            $query->selectRaw(
                'CASE WHEN tp.location IS NULL THEN NULL ELSE ST_Distance(tp.location::geography, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography) / 1000 END as distance_km',
                [(float) $deliveryLongitude, (float) $deliveryLatitude],
            );
        } else {
            $query->selectRaw('NULL::numeric as distance_km');
        }

        if (filled($deliveryWilaya)) {
            $query->selectRaw(
                'CASE WHEN tp.work_wilaya = ? THEN 0 ELSE 1 END as wilaya_rank',
                [(string) $deliveryWilaya],
            );
        } else {
            $query->selectRaw('1 as wilaya_rank');
        }

        return $query
            ->orderBy('wilaya_rank')
            ->orderByRaw('CASE WHEN tp.location IS NULL THEN 1 ELSE 0 END')
            ->orderBy('distance_km')
            ->limit(20)
            ->get();
    }

    /**
     * @param Collection<int, User> $tailors
     *
     * @return array<string, mixed>
     */
    public function buildMatchingSnapshot(Order $order, Collection $tailors): array
    {
        $order->loadMissing(['product.category']);

        /** @var User|null $recommendedTailor */
        $recommendedTailor = $tailors->first();

        return [
            'strategy' => 'specialization_then_nearest',
            'category_id' => $order->product?->category_id,
            'category_name' => $order->product?->category?->name,
            'resolved_specialization' => $this->resolveTargetSpecialization($order),
            'delivery_work_wilaya' => $order->delivery_work_wilaya,
            'eligible_tailors_count' => $tailors->count(),
            'recommended_tailor_id' => $recommendedTailor?->id,
            'recommended_distance_km' => $this->normalizedDistance($recommendedTailor),
            'ranked_tailors' => $tailors
                ->map(fn (User $tailor): array => [
                    'tailor_id' => $tailor->id,
                    'distance_km' => $this->normalizedDistance($tailor),
                    'work_wilaya' => $tailor->getAttribute('tailor_work_wilaya'),
                ])
                ->values()
                ->all(),
        ];
    }

    public function isTailorEligibleForOrder(User $tailor, Order $order): bool
    {
        if (
            $tailor->role !== User::ROLE_TAILOR
            || $tailor->is_suspended
            || $tailor->approved_at === null
            || (MailSetting::tailorPhoneVerificationEnabled() && filled($tailor->phone) && $tailor->phone_verified_at === null)
        ) {
            return false;
        }

        $order->loadMissing(['product.category']);
        $tailor->loadMissing('tailorProfile');

        $profile = $tailor->tailorProfile;

        if ($profile === null || $profile->status !== TailorProfile::STATUS_ONLINE) {
            return false;
        }

        $resolvedSpecialization = $this->resolveTargetSpecialization($order);

        if ($resolvedSpecialization !== null) {
            return (string) $profile->specialization === $resolvedSpecialization;
        }

        return (int) $profile->category_id === (int) ($order->product?->category_id ?? 0);
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
            ->mapWithKeys(fn (User $tailor): array => [
                (int) $tailor->id => $this->normalizedDistance($tailor),
            ])
            ->filter(fn ($distance): bool => $distance !== null)
            ->all();

        $tailors->each(function (User $tailor) use ($order, $distancesByTailorId): void {
            TailorOrderOffer::query()->updateOrCreate(
                [
                    'order_id' => $order->id,
                    'tailor_id' => $tailor->id,
                ],
                [
                    'status' => TailorOrderOffer::STATUS_UNREAD,
                    'distance_km' => $distancesByTailorId[(int) $tailor->id] ?? null,
                    'meta' => [
                        'matched_specialization' => $order->matched_specialization,
                        'recommended_tailor_id' => data_get($order->matching_snapshot, 'recommended_tailor_id'),
                    ],
                ],
            );

            $mailConfig = app(MailConfigurationService::class);
            $mailConfig->applyRuntimeConfiguration();

            if ($mailConfig->canSend()) {
                $mailable = new IncomingTailorOrderMail(
                    order: $order->fresh(['product.category', 'product.fabric']) ?? $order,
                    tailor: $tailor,
                    messageLocale: app()->getLocale(),
                );

                $pendingMail = Mail::to($tailor->email);

                if ($mailConfig->shouldQueue()) {
                    $pendingMail->queue($mailable);
                } else {
                    $pendingMail->send($mailable);
                }
            } else {
                $mailConfig->logSkipped('incoming-tailor-order', [
                    'order_id' => $order->id,
                    'tailor_id' => $tailor->id,
                ]);
            }
        });

        Event::dispatch(new OrderCreated($order, $tailorIds, $distancesByTailorId));
    }

    private function resolveTargetSpecialization(Order $order): ?string
    {
        $specialization = $order->product?->category?->tailor_specialization;

        return filled($specialization) ? (string) $specialization : null;
    }

    private function normalizedDistance(?User $tailor): ?float
    {
        if ($tailor === null) {
            return null;
        }

        $distance = $tailor->getAttribute('distance_km');

        if ($distance === null || $distance === '') {
            return null;
        }

        return round((float) $distance, 2);
    }
}
