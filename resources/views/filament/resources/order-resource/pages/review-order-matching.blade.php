@php
    $orderContext = $this->reviewData['order_context'] ?? [];
    $assignment = $this->reviewData['assignment'] ?? [];
    $recommended = $this->reviewData['recommended_tailor'] ?? null;
    $rankedTailors = $this->reviewData['ranked_tailors'] ?? [];
    $meta = $this->reviewData['meta'] ?? [];
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section
            heading="Order Context"
            icon="heroicon-o-map-pin"
            description="Operational context used by specialization and nearest-tailor ranking."
        >
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/60">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Order</p>
                    <p class="mt-1 text-lg font-semibold text-slate-900 dark:text-white">#{{ $orderContext['order_id'] ?? '-' }}</p>
                    <div class="mt-3">
                        <x-filament::badge :color="$orderContext['status_color'] ?? 'gray'">
                            {{ $orderContext['status_label'] ?? 'Unknown' }}
                        </x-filament::badge>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/60">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Product & Category</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">{{ $orderContext['product_name'] ?? '-' }}</p>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $orderContext['category_name'] ?? '-' }}</p>
                    <p class="mt-3 text-xs text-slate-500">Resolved specialization</p>
                    <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $orderContext['resolved_specialization'] ?? '-' }}</p>
                    <p class="mt-3 text-xs text-slate-500">Fabric type / country</p>
                    <p class="text-sm font-medium text-slate-900 dark:text-white">
                        {{ $orderContext['fabric_type'] ?? '-' }} / {{ $orderContext['fabric_country'] ?? '-' }}
                    </p>
                    @if (filled($orderContext['fabric_description'] ?? null))
                        <p class="mt-2 text-xs text-slate-600 dark:text-slate-300">{{ $orderContext['fabric_description'] }}</p>
                    @endif
                    @if (filled($orderContext['fabric_image_url'] ?? null))
                        <img
                            src="{{ $orderContext['fabric_image_url'] }}"
                            alt="Fabric reference"
                            class="mt-3 h-24 w-24 rounded-lg border border-slate-200 object-cover dark:border-slate-700"
                        >
                    @endif
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/60">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Order Location</p>
                    <p class="mt-1 text-sm font-medium text-slate-900 dark:text-white">{{ $orderContext['delivery']['work_wilaya'] ?? '-' }}</p>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">{{ $orderContext['delivery']['label'] ?? 'No location label' }}</p>
                    <p class="mt-3 text-xs text-slate-500">
                        {{ $orderContext['delivery']['latitude'] !== null ? number_format((float) $orderContext['delivery']['latitude'], 5) : '-' }},
                        {{ $orderContext['delivery']['longitude'] !== null ? number_format((float) $orderContext['delivery']['longitude'], 5) : '-' }}
                    </p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/60">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Recommendation Status</p>
                    <div class="mt-1">
                        <x-filament::badge :color="$assignment['assignment_status_color'] ?? 'gray'">
                            {{ $assignment['assignment_status_label'] ?? 'Unknown' }}
                        </x-filament::badge>
                    </div>
                    <p class="mt-3 text-xs text-slate-500">Source</p>
                    <p class="text-sm text-slate-700 dark:text-slate-200">{{ $orderContext['recommendation_source'] ?? '-' }}</p>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section
            heading="Recommended Tailor"
            icon="heroicon-o-sparkles"
            description="Best current candidate from specialization + location-aware ranking."
        >
            @if ($recommended)
                <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto]">
                    <div class="rounded-xl border border-amber-200 bg-amber-50/60 p-4 dark:border-amber-500/30 dark:bg-amber-500/10">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $recommended['name'] ?? '-' }}</h3>
                            <x-filament::badge color="warning">Recommended</x-filament::badge>
                            @if (($recommended['is_same_wilaya'] ?? false) === true)
                                <x-filament::badge color="info">Same Wilaya</x-filament::badge>
                            @endif
                            @if (($recommended['distance_km'] ?? null) !== null)
                                <x-filament::badge color="primary">Closest Eligible Tailor</x-filament::badge>
                            @endif
                        </div>

                        <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Specialization</p>
                                <p class="mt-1 text-sm font-medium text-slate-900 dark:text-white">{{ $recommended['specialization'] ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Work Wilaya</p>
                                <p class="mt-1 text-sm font-medium text-slate-900 dark:text-white">{{ $recommended['work_wilaya'] ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Distance</p>
                                <p class="mt-1 text-sm font-medium text-slate-900 dark:text-white">
                                    {{ ($recommended['distance_km'] ?? null) !== null ? number_format((float) $recommended['distance_km'], 2).' km' : 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Experience / Workers</p>
                                <p class="mt-1 text-sm font-medium text-slate-900 dark:text-white">
                                    {{ $recommended['years_of_experience'] ?? '-' }} yrs - {{ $recommended['workers_count'] ?? '-' }} workers
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <x-filament::badge :color="($recommended['is_phone_verified'] ?? false) ? 'success' : 'warning'">
                                {{ ($recommended['is_phone_verified'] ?? false) ? 'Phone verified' : 'Phone not verified' }}
                            </x-filament::badge>
                            <x-filament::badge :color="($recommended['is_approved'] ?? false) ? 'success' : 'warning'">
                                {{ ($recommended['is_approved'] ?? false) ? 'Approved' : 'Pending approval' }}
                            </x-filament::badge>
                            <x-filament::badge :color="($recommended['availability_status'] ?? null) === 'online' ? 'primary' : 'gray'">
                                {{ str((string) ($recommended['availability_status'] ?? 'offline'))->headline() }}
                            </x-filament::badge>
                        </div>
                    </div>

                    <div class="flex items-start lg:justify-end">
                        @if (filled($recommended['resource_url'] ?? null))
                            <x-filament::button
                                tag="a"
                                :href="$recommended['resource_url']"
                                icon="heroicon-o-user-circle"
                                color="info"
                            >
                                Open Tailor Profile
                            </x-filament::button>
                        @endif
                    </div>
                </div>
            @else
                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-600 dark:border-slate-600 dark:bg-slate-900/40 dark:text-slate-300">
                    No eligible tailor recommendation is currently available for this order. Check specialization mapping, tailor approval, and location coverage.
                </div>
            @endif
        </x-filament::section>

        <x-filament::section
            heading="Ranked Eligible Tailors"
            icon="heroicon-o-list-bullet"
            description="Candidates sorted by specialization rules, wilaya proximity, and distance when coordinates are available."
        >
            @if (empty($rankedTailors))
                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-600 dark:border-slate-600 dark:bg-slate-900/40 dark:text-slate-300">
                    No eligible tailors were found for this order.
                    @if (($meta['used_snapshot_fallback'] ?? false) === true)
                        <span class="block mt-2">Stored snapshot fallback was used, but no valid tailor records are currently available.</span>
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-[900px] w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                        <thead class="bg-slate-50/80 dark:bg-slate-900/80">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">#</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Tailor</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Profile</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Distance</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Operational Status</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Matching Reasons</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-700 dark:bg-transparent">
                            @foreach ($rankedTailors as $tailor)
                                <tr @class([
                                    'bg-primary-50/50 dark:bg-primary-500/10' => ($tailor['is_recommended'] ?? false) === true,
                                ])>
                                    <td class="px-3 py-3 align-top">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-slate-900 dark:text-white">#{{ $tailor['position'] ?? '-' }}</span>
                                            @if (($tailor['is_recommended'] ?? false) === true)
                                                <x-filament::badge color="warning">Best Match</x-filament::badge>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 align-top">
                                        <p class="font-medium text-slate-900 dark:text-white">{{ $tailor['name'] ?? '-' }}</p>
                                        <p class="text-xs text-slate-500">{{ $tailor['email'] ?? '-' }}</p>
                                    </td>
                                    <td class="px-3 py-3 align-top">
                                        <p class="text-slate-700 dark:text-slate-200">{{ $tailor['specialization'] ?? '-' }}</p>
                                        <p class="text-xs text-slate-500">{{ $tailor['work_wilaya'] ?? '-' }}</p>
                                        <p class="text-xs text-slate-500 mt-1">
                                            {{ $tailor['years_of_experience'] ?? '-' }} yrs - {{ $tailor['workers_count'] ?? '-' }} workers
                                        </p>
                                    </td>
                                    <td class="px-3 py-3 align-top text-slate-700 dark:text-slate-200">
                                        {{ ($tailor['distance_km'] ?? null) !== null ? number_format((float) $tailor['distance_km'], 2).' km' : 'N/A' }}
                                    </td>
                                    <td class="px-3 py-3 align-top">
                                        <div class="flex flex-wrap gap-2">
                                            <x-filament::badge :color="($tailor['is_phone_verified'] ?? false) ? 'success' : 'warning'">
                                                {{ ($tailor['is_phone_verified'] ?? false) ? 'Phone verified' : 'Phone not verified' }}
                                            </x-filament::badge>
                                            <x-filament::badge :color="($tailor['is_approved'] ?? false) ? 'success' : 'warning'">
                                                {{ ($tailor['is_approved'] ?? false) ? 'Approved' : 'Pending approval' }}
                                            </x-filament::badge>
                                            <x-filament::badge :color="($tailor['availability_status'] ?? null) === 'online' ? 'primary' : 'gray'">
                                                {{ str((string) ($tailor['availability_status'] ?? 'offline'))->headline() }}
                                            </x-filament::badge>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 align-top">
                                        <div class="flex flex-wrap gap-2">
                                            @foreach (($tailor['reasons'] ?? []) as $reason)
                                                <x-filament::badge :color="$reason['color'] ?? 'gray'">
                                                    {{ $reason['label'] ?? '-' }}
                                                </x-filament::badge>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 align-top text-right">
                                        @if (filled($tailor['resource_url'] ?? null))
                                            <x-filament::button
                                                tag="a"
                                                :href="$tailor['resource_url']"
                                                icon="heroicon-o-arrow-top-right-on-square"
                                                color="gray"
                                                size="sm"
                                            >
                                                View
                                            </x-filament::button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>

