<?php

namespace App\Http\Resources;

use App\Support\OrderLifecycle;
use App\Services\OrderFinancialsService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = (string) $this->status;
        $user = $request->user();
        $isAcceptedTailor = $user?->role === 'tailor' && (int) $this->tailor_id === (int) $user->id;
        $isAdminOrCustomer = in_array($user?->role, ['admin', 'customer'], true);
        $canSeeSensitiveFulfillment = $isAdminOrCustomer || $isAcceptedTailor;
        $offer = $this->relationLoaded('tailorOffers') && $user?->role === 'tailor'
            ? $this->tailorOffers->firstWhere('tailor_id', $user->id)
            : null;
        $financials = app(OrderFinancialsService::class)->payload($this->resource);

        return [
            'id' => $this->id,
            'status' => $status,
            'customer_id' => $this->customer_id,
            'tailor_id' => $this->tailor_id,
            'product_id' => $this->product_id,
            'measurements' => $this->measurements,
            'delivery' => [
                'latitude' => $canSeeSensitiveFulfillment ? $this->delivery_latitude : null,
                'longitude' => $canSeeSensitiveFulfillment ? $this->delivery_longitude : null,
                'work_wilaya' => $this->delivery_work_wilaya,
                'label' => $canSeeSensitiveFulfillment ? $this->delivery_location_label : null,
                'preview' => trim(collect([$this->delivery_work_wilaya, $canSeeSensitiveFulfillment ? $this->delivery_location_label : null])->filter()->implode(' - ')) ?: null,
                'is_limited' => ! $canSeeSensitiveFulfillment,
            ],
            'financials' => $financials,
            'fulfillment' => [
                'pattern_file_url' => $canSeeSensitiveFulfillment ? $this->product?->pattern_file_url : null,
                'pattern_available' => filled($this->product?->pattern_file_path),
                'pattern_locked' => filled($this->product?->pattern_file_path) && ! $canSeeSensitiveFulfillment,
            ],
            'tailor_offer' => $offer ? [
                'id' => $offer->id,
                'status' => $offer->status,
                'is_unread' => $offer->read_at === null,
                'read_at' => optional($offer->read_at)?->toISOString(),
                'responded_at' => optional($offer->responded_at)?->toISOString(),
                'reason' => $offer->reason,
                'note' => $offer->note,
                'distance_km' => $offer->distance_km,
            ] : null,
            'matched_specialization' => $this->matched_specialization,
            'matching_snapshot' => $this->matching_snapshot,
            'cancellation_reason' => $this->cancellation_reason,
            'timestamps' => [
                'accepted_at' => optional($this->accepted_at)?->toISOString(),
                'created_at' => optional($this->created_at)?->toISOString(),
                'updated_at' => optional($this->updated_at)?->toISOString(),
            ],
            'product' => new ProductResource($this->whenLoaded('product')),
            'customer' => $this->whenLoaded('customer', fn (): array => $canSeeSensitiveFulfillment ? [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
            ] : [
                'id' => null,
                'name' => __('messages.orders.customer_hidden_until_acceptance'),
            ]),
            'tailor' => $this->whenLoaded('tailor', fn (): array => [
                'id' => $this->tailor->id,
                'name' => $this->tailor->name,
            ]),
            'review' => new ReviewResource($this->whenLoaded('review')),
            'lifecycle' => [
                'allowed_next_statuses_for_tailor' => OrderLifecycle::allowedTailorNextStatuses($status),
                'customer_can_cancel' => in_array($status, OrderLifecycle::customerCancellableStatuses(), true),
                'tailor_can_cancel' => in_array($status, OrderLifecycle::tailorCancellableStatuses(), true),
                'is_terminal' => in_array($status, OrderLifecycle::terminalStatuses(), true),
            ],
        ];
    }
}
