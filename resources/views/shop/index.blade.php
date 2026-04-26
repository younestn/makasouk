<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('shop.meta.title') }}</title>
    <meta name="description" content="{{ __('shop.meta.description') }}">
    @vite(['resources/css/app.css'])
</head>
<body>
<header class="public-header">
    <div class="container public-header-inner">
        <a class="brand" href="{{ url('/') }}">
            <span class="brand-mark">MK</span>
            <span>{{ __('shop.brand.shop_name') }}</span>
        </a>

        <nav class="public-nav" aria-label="{{ __('shop.nav.shop_navigation') }}">
            <a class="public-nav-link" href="{{ url('/') }}">{{ __('shop.nav.home') }}</a>
            <a class="public-nav-link" href="{{ route('shop.index') }}">{{ __('shop.nav.shop') }}</a>
            <a class="public-nav-link" href="{{ url('/app/login') }}">{{ __('shop.nav.login') }}</a>
            <a class="public-nav-link" href="{{ url('/admin-panel/login') }}">{{ __('shop.nav.admin') }}</a>
            <a class="public-nav-link" href="{{ route('locale.switch', ['locale' => app()->getLocale() === 'ar' ? 'en' : 'ar']) }}">
                {{ app()->getLocale() === 'ar' ? __('shop.nav.english') : __('shop.nav.arabic') }}
            </a>
        </nav>
    </div>
</header>

<main class="page">
    <section class="container stack" style="gap: 2rem;">
        @php
            $sectionOrder = $settings->section_order ?? ['hero', 'categories', 'new_arrivals', 'best_sellers', 'category_sections', 'all_products'];
        @endphp

        @foreach($sectionOrder as $section)
            @switch($section)
                @case('hero')
                    @if($settings->hero_enabled)
                        @include('shop.partials.hero-slider', ['banners' => $banners, 'settings' => $settings])
                    @endif
                    @break

                @case('categories')
                    @if($settings->category_blocks_enabled)
                        <section class="page-section">
                            @include('shop.partials.section-header', [
                                'eyebrow' => __('shop.sections.browse_eyebrow'),
                                'title' => __('shop.sections.browse_title'),
                                'description' => __('shop.sections.browse_description'),
                            ])

                            @include('shop.partials.category-blocks', ['categories' => $categories])
                        </section>
                    @endif
                    @break

                @case('new_arrivals')
                    @if($settings->new_arrivals_enabled)
                        <section class="page-section">
                            @include('shop.partials.section-header', [
                                'eyebrow' => __('shop.sections.new_arrivals_eyebrow'),
                                'title' => __('shop.sections.new_arrivals_title'),
                                'description' => __('shop.sections.new_arrivals_description'),
                            ])

                            @if($newArrivals->isEmpty())
                                @include('shop.partials.empty-state', [
                                    'title' => __('shop.empty.no_new_arrivals_title'),
                                    'message' => __('shop.empty.no_new_arrivals_message'),
                                ])
                            @else
                                <div class="grid grid-4">
                                    @foreach($newArrivals as $product)
                                        @include('shop.partials.product-card', ['product' => $product])
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    @endif
                    @break

                @case('best_sellers')
                    @if($settings->best_sellers_enabled)
                        <section class="page-section">
                            @include('shop.partials.section-header', [
                                'eyebrow' => __('shop.sections.best_sellers_eyebrow'),
                                'title' => __('shop.sections.best_sellers_title'),
                                'description' => __('shop.sections.best_sellers_description'),
                            ])

                            @if($bestSellers->isEmpty())
                                @include('shop.partials.empty-state', [
                                    'title' => __('shop.empty.no_best_sellers_title'),
                                    'message' => __('shop.empty.no_best_sellers_message'),
                                ])
                            @else
                                <div class="grid grid-4">
                                    @foreach($bestSellers as $product)
                                        @include('shop.partials.product-card', ['product' => $product])
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    @endif
                    @break

                @case('category_sections')
                    @if($settings->category_sections_enabled)
                        <section class="page-section">
                            @include('shop.partials.section-header', [
                                'eyebrow' => __('shop.sections.featured_categories_eyebrow'),
                                'title' => __('shop.sections.featured_categories_title'),
                                'description' => __('shop.sections.featured_categories_description'),
                            ])

                            @if($categorySections->isEmpty())
                                @include('shop.partials.empty-state', [
                                    'title' => __('shop.empty.no_featured_categories_title'),
                                    'message' => __('shop.empty.no_featured_categories_message'),
                                ])
                            @else
                                <div class="stack" style="gap:2rem;">
                                    @foreach($categorySections as $category)
                                        <section class="stack">
                                            <div class="row" style="justify-content: space-between; align-items: end;">
                                                <div>
                                                    <h3 class="title">{{ $category->name }}</h3>
                                                    <p class="small">{{ $category->description ?: __('shop.sections.category_fallback_description') }}</p>
                                                </div>
                                                <a class="ui-btn ui-btn--secondary ui-btn--sm" href="{{ route('shop.category', $category->slug) }}">{{ __('shop.actions.view_more') }}</a>
                                            </div>

                                            @if($category->products->isEmpty())
                                                @include('shop.partials.empty-state', [
                                                    'title' => __('shop.empty.no_products_in_category_title'),
                                                    'message' => __('shop.empty.no_products_in_category_message'),
                                                ])
                                            @else
                                                <div class="grid grid-4">
                                                    @foreach($category->products as $product)
                                                        @include('shop.partials.product-card', ['product' => $product])
                                                    @endforeach
                                                </div>
                                            @endif
                                        </section>
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    @endif
                    @break

                @case('all_products')
                    @if($settings->all_products_enabled)
                        <section class="page-section" id="all-products">
                            @include('shop.partials.section-header', [
                                'eyebrow' => __('shop.sections.all_products_eyebrow'),
                                'title' => __('shop.sections.all_products_title'),
                                'description' => __('shop.sections.all_products_description'),
                            ])

                            <form class="shop-filters card" action="{{ route('shop.index') }}" method="GET">
                                <div class="grid grid-4">
                                    <div>
                                        <label class="label" for="q">{{ __('shop.filters.search') }}</label>
                                        <input class="input" id="q" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('shop.filters.search_placeholder') }}">
                                    </div>

                                    <div>
                                        <label class="label" for="category">{{ __('shop.filters.category') }}</label>
                                        <select class="select" id="category" name="category">
                                            <option value="">{{ __('shop.filters.all_categories') }}</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="label" for="sort">{{ __('shop.filters.sort_by') }}</label>
                                        <select class="select" id="sort" name="sort">
                                            <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>{{ __('shop.filters.newest') }}</option>
                                            <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>{{ __('shop.filters.price_asc') }}</option>
                                            <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>{{ __('shop.filters.price_desc') }}</option>
                                            <option value="best_selling" @selected(($filters['sort'] ?? '') === 'best_selling')>{{ __('shop.filters.best_selling') }}</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="label" for="min_price">{{ __('shop.filters.min_price') }}</label>
                                        <input class="input" id="min_price" type="number" step="0.01" min="0" name="min_price" value="{{ $filters['min_price'] ?? '' }}">
                                    </div>

                                    <div>
                                        <label class="label" for="max_price">{{ __('shop.filters.max_price') }}</label>
                                        <input class="input" id="max_price" type="number" step="0.01" min="0" name="max_price" value="{{ $filters['max_price'] ?? '' }}">
                                    </div>
                                </div>

                                <div class="row" style="justify-content: space-between; margin-top: 1rem;">
                                    <div class="row">
                                        <label class="row"><input type="checkbox" name="featured" value="1" @checked(($filters['featured'] ?? null) == 1)> {{ __('shop.filters.featured') }}</label>
                                        <label class="row"><input type="checkbox" name="best_seller" value="1" @checked(($filters['best_seller'] ?? null) == 1)> {{ __('shop.filters.best_seller') }}</label>
                                        <label class="row"><input type="checkbox" name="in_stock" value="1" @checked(($filters['in_stock'] ?? null) == 1)> {{ __('shop.filters.in_stock') }}</label>
                                    </div>
                                    <div class="row">
                                        <a class="ui-btn ui-btn--ghost" href="{{ route('shop.index') }}#all-products">{{ __('shop.actions.reset') }}</a>
                                        <button class="ui-btn ui-btn--primary" type="submit">{{ __('shop.actions.apply_filters') }}</button>
                                    </div>
                                </div>
                            </form>

                            @if($allProducts->isEmpty())
                                @include('shop.partials.empty-state', [
                                    'title' => __('shop.empty.no_products_match_title'),
                                    'message' => __('shop.empty.no_products_match_message'),
                                ])
                            @else
                                <div class="grid grid-4">
                                    @foreach($allProducts as $product)
                                        @include('shop.partials.product-card', ['product' => $product])
                                    @endforeach
                                </div>

                                <div class="ui-pagination" style="margin-top: 1.2rem;">
                                    <div class="small">
                                        {{ __('shop.pagination.showing', ['from' => $allProducts->firstItem(), 'to' => $allProducts->lastItem(), 'total' => $allProducts->total()]) }}
                                    </div>
                                    <div class="row">
                                        @if($allProducts->onFirstPage())
                                            <span class="ui-btn ui-btn--disabled ui-btn--sm">{{ __('shop.pagination.previous') }}</span>
                                        @else
                                            <a class="ui-btn ui-btn--sm" href="{{ $allProducts->previousPageUrl() }}">{{ __('shop.pagination.previous') }}</a>
                                        @endif

                                        <span class="small">{{ __('shop.pagination.page', ['current' => $allProducts->currentPage(), 'last' => $allProducts->lastPage()]) }}</span>

                                        @if($allProducts->hasMorePages())
                                            <a class="ui-btn ui-btn--sm" href="{{ $allProducts->nextPageUrl() }}">{{ __('shop.pagination.next') }}</a>
                                        @else
                                            <span class="ui-btn ui-btn--disabled ui-btn--sm">{{ __('shop.pagination.next') }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </section>
                    @endif
                    @break
            @endswitch
        @endforeach
    </section>
</main>

<footer class="public-footer">
    <div class="container public-footer-inner">
        <p class="small">{{ __('shop.footer.copyright', ['year' => now()->year]) }}</p>
        <div class="row">
            <a class="public-footer-link" href="{{ route('shop.index') }}">{{ __('shop.nav.shop') }}</a>
            <a class="public-footer-link" href="{{ url('/contact') }}">{{ __('shop.footer.contact') }}</a>
        </div>
    </div>
</footer>

<script>
    document.querySelectorAll('[data-shop-hero]').forEach((hero) => {
        const slides = Array.from(hero.querySelectorAll('[data-shop-hero-slide]'));
        const dots = Array.from(hero.querySelectorAll('[data-shop-hero-dot]'));
        const prev = hero.querySelector('[data-shop-hero-prev]');
        const next = hero.querySelector('[data-shop-hero-next]');

        if (!slides.length) {
            return;
        }

        let activeIndex = 0;
        let timer = null;
        const autoplayEnabled = hero.dataset.autoplay === '1';
        const autoplayDelay = Number(hero.dataset.delay || 6000);

        const render = (index) => {
            activeIndex = (index + slides.length) % slides.length;

            slides.forEach((slide, slideIndex) => {
                slide.classList.toggle('is-active', slideIndex === activeIndex);
            });

            dots.forEach((dot, dotIndex) => {
                dot.classList.toggle('is-active', dotIndex === activeIndex);
            });
        };

        const startAutoplay = () => {
            if (!autoplayEnabled || slides.length < 2) {
                return;
            }

            clearInterval(timer);
            timer = setInterval(() => render(activeIndex + 1), autoplayDelay);
        };

        prev?.addEventListener('click', () => {
            render(activeIndex - 1);
            startAutoplay();
        });

        next?.addEventListener('click', () => {
            render(activeIndex + 1);
            startAutoplay();
        });

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                render(index);
                startAutoplay();
            });
        });

        render(0);
        startAutoplay();
    });
</script>
</body>
</html>

