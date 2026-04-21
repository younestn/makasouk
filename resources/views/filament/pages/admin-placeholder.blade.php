<x-filament-panels::page>
    <x-filament::section
        icon="heroicon-o-light-bulb"
        :heading="$this->getPlaceholderTitle()"
        description="This module is scaffolded for expansion and aligned with the new admin information architecture."
    >
        <div class="space-y-4">
            <div class="rounded-xl border border-dashed border-primary-300 bg-primary-50/50 p-4 text-sm text-primary-800 dark:border-primary-500/60 dark:bg-primary-950/30 dark:text-primary-200">
                Placeholder module enabled. You can continue implementation in small safe increments without changing the sidebar architecture.
            </div>

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($this->getPlannedCapabilities() as $capability)
                    <article class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
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
