<x-filament-widgets::widget>
    <x-filament::section
        icon="heroicon-o-bolt"
        heading="Quick Actions"
        description="Shortcuts for the most common admin tasks."
    >
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($actions as $action)
                <a
                    href="{{ $action['url'] }}"
                    class="mk-admin-quick-action group flex items-start gap-3 rounded-xl border border-gray-200 bg-white p-4 transition hover:-translate-y-0.5 hover:border-primary-300 hover:shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <span class="rounded-lg bg-primary-50 p-2 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                        <x-filament::icon :icon="$action['icon']" class="h-5 w-5" />
                    </span>
                    <span class="space-y-1">
                        <span class="block text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $action['label'] }}
                        </span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400">
                            {{ $action['description'] }}
                        </span>
                    </span>
                </a>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
