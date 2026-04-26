<x-filament-panels::page>
    <x-filament::section
        icon="heroicon-o-light-bulb"
        :heading="$this->getPlaceholderTitle()"
        description="This module is scaffolded for phased implementation within the atelier admin architecture."
    >
        <div class="space-y-4">
            <div class="rounded-xl border border-amber-100 bg-amber-50/70 p-4 text-sm text-amber-900 dark:border-amber-400/25 dark:bg-amber-500/10 dark:text-amber-200">
                Placeholder module enabled. Continue implementation in safe increments while preserving navigation and permissions.
            </div>

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($this->getPlannedCapabilities() as $capability)
                    <article class="rounded-xl border border-amber-100/70 bg-white/95 p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-md dark:border-gray-800 dark:bg-gray-900/95">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $capability['title'] }}
                        </h3>
                        <p class="mt-2 text-xs leading-5 text-gray-500 dark:text-gray-400">
                            {{ $capability['description'] }}
                        </p>
                    </article>
                @endforeach
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>