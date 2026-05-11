<?php

namespace App\Http\Requests\Customer;

use App\Models\Product;
use App\Support\Geo\AlgeriaBounds;
use App\Support\Tailor\TailorOnboardingOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->role === 'customer';
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'measurements' => ['required', 'array', 'min:1'],
            'measurements.*' => ['nullable'],
            'configuration' => ['nullable', 'array'],
            'configuration.color' => ['nullable', 'string', 'max:100'],
            'configuration.fabric' => ['nullable', 'string', 'max:100'],
            'customer_location' => ['required', 'array'],
            'customer_location.latitude' => ['required', 'numeric', 'between:-90,90'],
            'customer_location.longitude' => ['required', 'numeric', 'between:-180,180'],
            'customer_location.work_wilaya' => ['required', Rule::in(TailorOnboardingOptions::WILAYAS)],
            'customer_location.label' => ['nullable', 'string', 'max:255'],
            'shipping' => ['required', 'array'],
            'shipping.company_id' => ['required', 'integer', Rule::exists('shipping_companies', 'id')->where(fn ($query) => $query->where('is_active', true))],
            'shipping.delivery_type' => ['required', Rule::in(['office_pickup'])],
            'shipping.commune' => ['required', 'string', 'max:120'],
            'shipping.neighborhood' => ['required', 'string', 'max:120'],
            'shipping.phone' => ['required', 'string', 'max:40'],
            'shipping.email' => ['required', 'email', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $latitude = $this->input('customer_location.latitude');
            $longitude = $this->input('customer_location.longitude');

            if (is_numeric($latitude) && is_numeric($longitude) && ! AlgeriaBounds::contains((float) $latitude, (float) $longitude)) {
                $validator->errors()->add('customer_location.latitude', __('messages.validation.location_must_be_in_algeria'));
            }

            $productId = (int) $this->input('product_id');

            if ($productId <= 0) {
                return;
            }

            $product = Product::query()
                ->with(['measurements' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('name')])
                ->find($productId);

            if ($product === null) {
                return;
            }

            $rawMeasurements = $this->input('measurements', []);

            if (! is_array($rawMeasurements)) {
                $validator->errors()->add('measurements', __('messages.validation.measurements_key_value_required'));
                return;
            }

            $measurementDefinitions = $product->measurements;

            if ($measurementDefinitions->isEmpty()) {
                $this->validateLegacyMeasurements($validator, $rawMeasurements);
                return;
            }

            $allowedSlugs = $measurementDefinitions->pluck('slug')->all();

            foreach ($measurementDefinitions as $measurementDefinition) {
                $slug = (string) $measurementDefinition->slug;

                if (! array_key_exists($slug, $rawMeasurements)) {
                    $validator->errors()->add("measurements.$slug", sprintf(
                        '%s',
                        __('messages.validation.measurement_required_for_product', ['name' => $measurementDefinition->name]),
                    ));
                    continue;
                }

                $value = $rawMeasurements[$slug];

                if (! is_numeric($value) || (float) $value <= 0 || (float) $value > 350) {
                    $validator->errors()->add("measurements.$slug", sprintf(
                        '%s',
                        __('messages.validation.measurement_invalid_for_product', ['name' => $measurementDefinition->name]),
                    ));
                }
            }

            $extraKeys = array_diff(array_keys($rawMeasurements), $allowedSlugs);

            if ($extraKeys !== []) {
                $validator->errors()->add(
                    'measurements',
                    __('messages.validation.measurements_invalid_fields'),
                );
            }

            $configuration = $this->input('configuration', []);

            if (! is_array($configuration)) {
                $validator->errors()->add('configuration', __('messages.validation.order_configuration_invalid'));
                return;
            }

            $availableColorKeys = collect($product->color_options ?? [])
                ->filter(fn ($option): bool => is_array($option) && filled($option['key'] ?? null))
                ->map(fn ($option): string => (string) $option['key'])
                ->values()
                ->all();

            if ($availableColorKeys !== []) {
                $selectedColor = (string) ($configuration['color'] ?? '');

                if ($selectedColor === '') {
                    $validator->errors()->add('configuration.color', __('messages.validation.color_selection_required'));
                } elseif (! in_array($selectedColor, $availableColorKeys, true)) {
                    $validator->errors()->add('configuration.color', __('messages.validation.color_selection_invalid'));
                }
            }

            $availableFabricKeys = collect($product->availableFabricOptions())
                ->pluck('key')
                ->filter()
                ->values()
                ->all();

            if ($availableFabricKeys !== []) {
                $selectedFabric = (string) ($configuration['fabric'] ?? '');

                if ($selectedFabric === '') {
                    $validator->errors()->add('configuration.fabric', __('messages.validation.fabric_selection_required'));
                } elseif (! in_array($selectedFabric, $availableFabricKeys, true)) {
                    $validator->errors()->add('configuration.fabric', __('messages.validation.fabric_selection_invalid'));
                }
            }
        });
    }

    /**
     * @param array<string, mixed> $measurements
     */
    private function validateLegacyMeasurements($validator, array $measurements): void
    {
        $nonEmptyValues = collect($measurements)
            ->filter(fn ($value): bool => $value !== null && $value !== '')
            ->all();

        if ($nonEmptyValues === []) {
            $validator->errors()->add('measurements', __('messages.validation.measurements_at_least_one'));
            return;
        }

        foreach ($nonEmptyValues as $key => $value) {
            if (! is_numeric($value) || (float) $value <= 0 || (float) $value > 350) {
                $validator->errors()->add("measurements.$key", __('messages.validation.measurements_positive_numeric'));
            }
        }
    }
}
