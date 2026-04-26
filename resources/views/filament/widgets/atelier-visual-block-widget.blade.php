<x-filament-widgets::widget>
    <x-filament::section
        icon="heroicon-o-swatch"
        heading="Atelier Signature"
        description="A visual pulse inspired by fabric craft, precision, and quality finishes."
    >
        <div class="mk-atelier-visual-card">
            <div class="mk-atelier-visual-card__texture" aria-hidden="true"></div>

            <div class="space-y-3">
                <p class="text-xs uppercase tracking-[0.16em] text-amber-700/80 dark:text-amber-300/85">
                    Craftsmanship Focus
                </p>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                    Most requested service category: {{ $topCategory }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Completed-order revenue currently stands at <span class="font-semibold text-amber-700 dark:text-amber-300">{{ $revenue }}</span>.
                    Keep quality and turnaround balanced to sustain premium customer trust.
                </p>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
