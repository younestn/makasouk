<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'pricing_type' => $this->pricing_type,
            'price' => (float) $this->price,
            'sale_price' => $this->sale_price !== null ? (float) $this->sale_price : null,
            'stock' => (int) $this->stock,
            'sku' => $this->sku,
            'is_active' => (bool) $this->is_active,
            'is_featured' => (bool) $this->is_featured,
            'is_best_seller' => (bool) $this->is_best_seller,
            'published_at' => optional($this->published_at)?->toISOString(),
            'main_image_url' => $this->main_image_url,
            'fabric_id' => $this->fabric_id,
            'fabric_type' => $this->display_fabric_type,
            'fabric_country' => $this->display_fabric_country,
            'fabric_description' => $this->display_fabric_description,
            'fabric_image_url' => $this->fabric_image_url,
            'fabric' => [
                'id' => $this->fabric_id,
                'type' => $this->display_fabric_type,
                'country' => $this->display_fabric_country,
                'description' => $this->display_fabric_description,
                'image_url' => $this->fabric_image_url,
                'source' => $this->fabric_id ? 'library' : 'legacy',
            ],
            'category' => $this->whenLoaded('category', fn (): array => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
                'tailor_specialization' => $this->category->tailor_specialization,
            ]),
            'created_by_admin' => $this->whenLoaded('createdByAdmin', fn (): array => [
                'id' => $this->createdByAdmin->id,
                'name' => $this->createdByAdmin->name,
            ]),
            'measurements' => $this->whenLoaded('measurements', fn () => $this->measurements
                ->map(fn ($measurement): array => [
                    'id' => $measurement->id,
                    'name' => $measurement->name,
                    'slug' => $measurement->slug,
                    'audience' => $measurement->audience,
                    'description' => $measurement->description,
                    'guide_text' => $measurement->guide_text,
                    'helper_text' => $measurement->helper_text,
                    'guide_image_url' => filled($measurement->guide_image_path)
                        ? asset('storage/'.$measurement->guide_image_path)
                        : null,
                    'sort_order' => (int) $measurement->sort_order,
                    'unit' => 'cm',
                ])
                ->values()
                ->all()),
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
        ];
    }
}
