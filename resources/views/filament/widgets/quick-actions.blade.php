<x-filament-widgets::widget>
    <x-filament::section
        icon="heroicon-o-bolt"
        heading="Quick Actions"
        description="Immediate shortcuts for atelier operations, merchandising, and governance."
    >
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($actions as $action)
                <a
                    href="{{ $action['url'] }}"
                    class="mk-admin-quick-action group flex items-start gap-3 rounded-xl border border-amber-100/70 bg-white/95 p-4 transition hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-md dark:border-gray-800 dark:bg-gray-900/95"
                >
                    <span class="rounded-lg bg-amber-50 p-2 text-amber-700 transition group-hover:bg-amber-100 dark:bg-amber-400/10 dark:text-amber-300">
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
