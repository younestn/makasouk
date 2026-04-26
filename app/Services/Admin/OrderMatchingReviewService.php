<?php

namespace App\Services\Admin;

use App\Filament\Resources\UserResource;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderMatchingService;
use App\Support\Filament\AdminUiState;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class OrderMatchingReviewService
{
    public function __construct(private readonly OrderMatchingService $orderMatchingService)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(Order $order): array
    {
        $order->loadMissing([
            'product.category',
            'product.fabric',
            'customer',
            'tailor.tailorProfile',
        ]);

        $resolvedSpecialization = $order->matched_specialization ?: $order->product?->category?->tailor_specialization;
        $deliveryWilaya = $order->delivery_work_wilaya;

        $rankedTailors = $this->mapLiveEligibleTailors($order, $resolvedSpecialization, $deliveryWilaya);

        $usedSnapshotFallback = false;

        if ($rankedTailors === [] && filled(data_get($order->matching_snapshot, 'ranked_tailors'))) {
            $rankedTailors = $this->mapSnapshotTailors($order, $resolvedSpecialization, $deliveryWilaya);
            $usedSnapshotFallback = true;
        }

        $recommendedTailor = $rankedTailors[0] ?? null;

        return [
            'order_context' => [
                'order_id' => $order->id,
                'status' => (string) $order->status,
                'status_label' => AdminUiState::orderStatusLabel($order->status),
                'status_color' => AdminUiState::orderStatusColor($order->status),
                'product_name' => $order->product?->name,
                'category_name' => $order->product?->category?->name,
                'category_specialization' => $order->product?->category?->tailor_specialization,
                'resolved_specialization' => $resolvedSpecialization,
                'fabric_type' => $order->product?->display_fabric_type,
                'fabric_country' => $order->product?->display_fabric_country,
                'fabric_description' => $order->product?->display_fabric_description,
                'fabric_image_url' => $order->product?->fabric_image_url,
                'delivery' => [
                    'latitude' => $order->delivery_latitude,
                    'longitude' => $order->delivery_longitude,
                    'work_wilaya' => $order->delivery_work_wilaya,
                    'label' => $order->delivery_location_label,
                ],
                'matching_strategy' => data_get($order->matching_snapshot, 'strategy', 'specialization_then_nearest'),
                'recommendation_source' => $this->resolveRecommendationSource(
                    $resolvedSpecialization,
                    $rankedTailors,
                    $usedSnapshotFallback,
                ),
            ],
            'assignment' => [
                'assigned_tailor_id' => $order->tailor_id,
                'assigned_tailor_name' => $order->tailor?->name,
                'recommended_tailor_id' => $recommendedTailor['id'] ?? null,
                'recommended_tailor_name' => $recommendedTailor['name'] ?? null,
                'is_recommended_assigned' => $order->tailor_id !== null
                    && $order->tailor_id === ($recommendedTailor['id'] ?? null),
                ...$this->resolveAssignmentStatus(
                    assignedTailorId: $order->tailor_id,
                    recommendedTailorId: $recommendedTailor['id'] ?? null,
                ),
            ],
            'recommended_tailor' => $recommendedTailor,
            'ranked_tailors' => $rankedTailors,
            'meta' => [
                'eligible_tailors_count' => count($rankedTailors),
                'used_snapshot_fallback' => $usedSnapshotFallback,
                'snapshot_recommended_tailor_id' => data_get($order->matching_snapshot, 'recommended_tailor_id'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function recompute(Order $order): array
    {
        $order->loadMissing(['product.category', 'product.fabric']);

        $eligibleTailors = $this->orderMatchingService->findNearbyTailors($order, 20);
        $snapshot = $this->orderMatchingService->buildMatchingSnapshot($order, $eligibleTailors);

        $order->forceFill([
            'matched_specialization' => $snapshot['resolved_specialization'] ?? null,
            'matching_snapshot' => $snapshot,
        ])->save();

        return $this->build($order->fresh(['product.category', 'product.fabric', 'tailor.tailorProfile', 'customer']) ?? $order);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mapLiveEligibleTailors(Order $order, ?string $resolvedSpecialization, ?string $deliveryWilaya): array
    {
        $eligibleTailors = $this->orderMatchingService->findNearbyTailors($order, 20);
        $eligibleTailors->loadMissing('tailorProfile');
        $recommendedTailorId = (int) ($eligibleTailors->first()?->id ?? 0);

        return $eligibleTailors
            ->values()
            ->map(function (User $tailor, int $index) use ($order, $resolvedSpecialization, $deliveryWilaya, $recommendedTailorId): array {
                return $this->mapTailorForDisplay(
                    tailor: $tailor,
                    order: $order,
                    resolvedSpecialization: $resolvedSpecialization,
                    deliveryWilaya: $deliveryWilaya,
                    position: $index + 1,
                    recommendedTailorId: $recommendedTailorId,
                    isSnapshotFallback: false,
                );
            })
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mapSnapshotTailors(Order $order, ?string $resolvedSpecialization, ?string $deliveryWilaya): array
    {
        $rankedFromSnapshot = collect(data_get($order->matching_snapshot, 'ranked_tailors', []));

        if ($rankedFromSnapshot->isEmpty()) {
            return [];
        }

        $tailorIds = $rankedFromSnapshot
            ->pluck('tailor_id')
            ->filter(static fn ($id): bool => is_numeric($id))
            ->map(static fn ($id): int => (int) $id)
            ->values();

        /** @var EloquentCollection<int, User> $tailors */
        $tailors = User::query()
            ->with('tailorProfile')
            ->whereIn('id', $tailorIds)
            ->get()
            ->keyBy('id');

        $recommendedTailorId = (int) (data_get($order->matching_snapshot, 'recommended_tailor_id') ?? 0);

        return $rankedFromSnapshot
            ->values()
            ->map(function (array $snapshotRow, int $index) use (
                $tailors,
                $order,
                $resolvedSpecialization,
                $deliveryWilaya,
                $recommendedTailorId,
            ): ?array {
                $tailorId = (int) ($snapshotRow['tailor_id'] ?? 0);
                $tailor = $tailors->get($tailorId);

                if (! $tailor instanceof User) {
                    return null;
                }

                $tailor->setAttribute('distance_km', $snapshotRow['distance_km'] ?? null);
                $tailor->setAttribute('tailor_work_wilaya', $snapshotRow['work_wilaya'] ?? $tailor->tailorProfile?->work_wilaya);

                return $this->mapTailorForDisplay(
                    tailor: $tailor,
                    order: $order,
                    resolvedSpecialization: $resolvedSpecialization,
                    deliveryWilaya: $deliveryWilaya,
                    position: $index + 1,
                    recommendedTailorId: $recommendedTailorId,
                    isSnapshotFallback: true,
                );
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapTailorForDisplay(
        User $tailor,
        Order $order,
        ?string $resolvedSpecialization,
        ?string $deliveryWilaya,
        int $position,
        int $recommendedTailorId,
        bool $isSnapshotFallback,
    ): array {
        $profile = $tailor->tailorProfile;
        $distance = $this->normalizeDistance($tailor->getAttribute('distance_km'));

        $workWilaya = $tailor->getAttribute('tailor_work_wilaya') ?? $profile?->work_wilaya;
        $specialization = $profile?->specialization;

        $hasPhone = filled($tailor->phone);
        $isPhoneVerified = ! $hasPhone || $tailor->phone_verified_at !== null;
        $isApproved = $tailor->approved_at !== null;
        $isOnline = $profile?->status === 'online';
        $isSameWilaya = filled($deliveryWilaya) && filled($workWilaya) && $deliveryWilaya === $workWilaya;
        $isSpecializationMatch = filled($resolvedSpecialization) && $resolvedSpecialization === $specialization;
        $isLegacyCategoryFallback = ! filled($resolvedSpecialization)
            && (int) ($profile?->category_id ?? 0) === (int) ($order->product?->category_id ?? 0);

        $reasons = [];

        if ($isSpecializationMatch) {
            $reasons[] = ['label' => 'Matching specialization', 'color' => 'success'];
        }

        if ($isLegacyCategoryFallback) {
            $reasons[] = ['label' => 'Legacy category fallback', 'color' => 'warning'];
        }

        if ($isSameWilaya) {
            $reasons[] = ['label' => 'Same wilaya', 'color' => 'info'];
        }

        if ($distance !== null) {
            $reasons[] = [
                'label' => $position === 1 ? 'Closest eligible tailor' : 'Distance available',
                'color' => $position === 1 ? 'primary' : 'gray',
            ];
        } else {
            $reasons[] = ['label' => 'Missing location fallback', 'color' => 'warning'];
        }

        if ($isApproved) {
            $reasons[] = ['label' => 'Approved tailor', 'color' => 'success'];
        }

        if ($isPhoneVerified) {
            $reasons[] = ['label' => 'Phone verified', 'color' => 'success'];
        }

        if ($isOnline) {
            $reasons[] = ['label' => 'Online / available', 'color' => 'primary'];
        }

        if ($isSnapshotFallback) {
            $reasons[] = ['label' => 'From stored snapshot', 'color' => 'gray'];
        }

        $isRecommended = $recommendedTailorId > 0
            ? $tailor->id === $recommendedTailorId
            : $position === 1;

        return [
            'position' => $position,
            'id' => $tailor->id,
            'name' => $tailor->name,
            'email' => $tailor->email,
            'specialization' => $specialization,
            'work_wilaya' => $workWilaya,
            'distance_km' => $distance,
            'workers_count' => $profile?->workers_count,
            'years_of_experience' => $profile?->years_of_experience,
            'is_phone_verified' => $isPhoneVerified,
            'is_approved' => $isApproved,
            'availability_status' => $profile?->status,
            'is_same_wilaya' => $isSameWilaya,
            'is_specialization_match' => $isSpecializationMatch,
            'is_recommended' => $isRecommended,
            'reasons' => $reasons,
            'resource_url' => UserResource::getUrl('view', ['record' => $tailor]),
        ];
    }

    private function normalizeDistance(mixed $distance): ?float
    {
        if ($distance === null || $distance === '') {
            return null;
        }

        return round((float) $distance, 2);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rankedTailors
     */
    private function resolveRecommendationSource(?string $resolvedSpecialization, array $rankedTailors, bool $usedSnapshotFallback): string
    {
        if ($rankedTailors === []) {
            return 'No eligible tailor found for the current matching rules.';
        }

        if ($usedSnapshotFallback) {
            return 'Showing stored ranking snapshot because no currently eligible live candidates were found.';
        }

        $rankedTailorCollection = collect($rankedTailors);

        $hasDistance = $rankedTailorCollection->contains(
            static fn (array $tailor): bool => $tailor['distance_km'] !== null,
        );
        $hasSameWilaya = $rankedTailorCollection->contains(
            static fn (array $tailor): bool => (bool) $tailor['is_same_wilaya'],
        );

        if (! filled($resolvedSpecialization)) {
            return 'Legacy category fallback: category specialization was not available.';
        }

        if ($hasDistance) {
            return 'Specialization + nearest distance priority.';
        }

        if ($hasSameWilaya) {
            return 'Specialization + same wilaya fallback due to incomplete coordinate coverage.';
        }

        return 'Specialization-only fallback because location data was incomplete.';
    }

    /**
     * @return array{assignment_status_label: string, assignment_status_color: string}
     */
    private function resolveAssignmentStatus(?int $assignedTailorId, ?int $recommendedTailorId): array
    {
        if ($assignedTailorId === null && $recommendedTailorId === null) {
            return [
                'assignment_status_label' => 'No recommendation available yet',
                'assignment_status_color' => 'warning',
            ];
        }

        if ($assignedTailorId === null && $recommendedTailorId !== null) {
            return [
                'assignment_status_label' => 'Recommendation available, not assigned',
                'assignment_status_color' => 'info',
            ];
        }

        if ($assignedTailorId !== null && $recommendedTailorId !== null && $assignedTailorId === $recommendedTailorId) {
            return [
                'assignment_status_label' => 'Assigned to recommended tailor',
                'assignment_status_color' => 'success',
            ];
        }

        if ($assignedTailorId !== null && $recommendedTailorId !== null && $assignedTailorId !== $recommendedTailorId) {
            return [
                'assignment_status_label' => 'Assigned manually (different from recommendation)',
                'assignment_status_color' => 'warning',
            ];
        }

        return [
            'assignment_status_label' => 'Assigned without recommendation',
            'assignment_status_color' => 'gray',
        ];
    }
}
