<?php

namespace App\Http\Resources;

use App\Services\OrderFinancialsService;
use App\Support\OrderLifecycle;
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
            'configuration' => [
                'color' => data_get($this->order_configuration, 'color'),
                'fabric' => data_get($this->order_configuration, 'fabric'),
            ],
            'delivery' => [
                'latitude' => $canSeeSensitiveFulfillment ? $this->delivery_latitude : null,
                'longitude' => $canSeeSensitiveFulfillment ? $this->delivery_longitude : null,
                'work_wilaya' => $this->delivery_work_wilaya,
                'commune' => $this->delivery_commune,
                'neighborhood' => $this->delivery_neighborhood,
                'label' => $canSeeSensitiveFulfillment ? $this->delivery_location_label : null,
                'preview' => trim(collect([
                    $this->delivery_work_wilaya,
                    $this->delivery_commune,
                    $canSeeSensitiveFulfillment ? ($this->delivery_neighborhood ?: $this->delivery_location_label) : null,
                ])->filter()->implode(' - ')) ?: null,
                'is_limited' => ! $canSeeSensitiveFulfillment,
            ],
            'shipping' => [
                'company_id' => $this->shipping_company_id,
                'company_name' => $this->shipping_company_name ?: $this->shippingCompany?->display_name,
                'delivery_type' => $this->delivery_type,
                'phone' => $canSeeSensitiveFulfillment ? $this->delivery_phone : null,
                'email' => $canSeeSensitiveFulfillment ? $this->delivery_email : null,
                'is_limited' => ! $canSeeSensitiveFulfillment,
            ],
            'financials' => $financials,
            'fulfillment' => [
                'pattern_file_url' => $canSeeSensitiveFulfillment ? $this->product?->pattern_file_url : null,
                'pattern_file_urls' => $canSeeSensitiveFulfillment ? ($this->product?->pattern_file_urls ?? []) : [],
                'pattern_available' => (bool) ($this->product?->has_pattern_files ?? false),
                'pattern_locked' => (bool) ($this->product?->has_pattern_files ?? false) && ! $canSeeSensitiveFulfillment,
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
