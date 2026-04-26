<x-filament-widgets::widget>
    <section class="mk-atelier-hero rounded-2xl border border-amber-100/70 p-6 sm:p-8">
        <div class="mk-atelier-hero__grid">
            <div class="space-y-5">
                <span class="mk-atelier-pill">
                    <x-filament::icon icon="heroicon-o-sparkles" class="h-4 w-4" />
                    Atelier Operations Suite
                </span>

                <div class="space-y-2">
                    <h2 class="mk-atelier-title">
                        Luxury Tailoring Command Center
                    </h2>
                    <p class="mk-atelier-subtitle">
                        Coordinate bespoke orders, monitor atelier flow, and keep customer craftsmanship standards exceptional.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <x-filament::button tag="a" :href="$storeUrl" color="warning" icon="heroicon-o-building-storefront" class="mk-atelier-cta">
                        Enter Store
                    </x-filament::button>

                    <x-filament::button tag="a" :href="$ordersUrl" color="gray" outlined icon="heroicon-o-clipboard-document-list">
                        Review Orders
                    </x-filament::button>
                </div>
            </div>

            <div class="mk-atelier-visual">
                <div class="mk-atelier-visual__panel">
                    <p class="mk-atelier-visual__label">Daily Atelier Pulse</p>
                    <div class="grid gap-3 sm:grid-cols-3">
                        @foreach ($heroStatItems as $item)
                            <article class="mk-atelier-mini-stat">
                                <span class="mk-atelier-mini-stat__icon">
                                    <x-filament::icon :icon="$item['icon']" class="h-4 w-4" />
                                </span>
                                <p class="mk-atelier-mini-stat__value">{{ $item['value'] }}</p>
                                <p class="mk-atelier-mini-stat__label">{{ $item['label'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-filament-widgets::widget>
