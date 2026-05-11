<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $product->name }} | {{ __('shop.brand.shop_name') }}</title>
    <meta name="description" content="{{ $product->short_description ?: \Illuminate\Support\Str::limit($product->description, 140) }}">
    @vite(['resources/css/app.css', 'resources/css/theme/shop-product-card.css'])
</head>
<body>
@php
    $galleryImages = $product->gallery_image_urls;
    $activeImage = $galleryImages[0] ?? null;
    $ratingAverage = $product->reviews_avg_rating !== null ? round((float) $product->reviews_avg_rating, 1) : null;
    $reviewsCount = (int) ($product->reviews_count ?? 0);
    $specifications = $product->localizedSpecifications();
    $orderNowUrl = url('/app/customer/orders/create?productId='.$product->id);
    $productCategory = $product->category;
    $productCategoryName = $productCategory?->display_name;
    $productCategoryUrl = $productCategory ? route('shop.category', $productCategory->slug) : null;
@endphp

<header class="public-header">
    <div class="container public-header-inner">
        <a class="brand" href="{{ route('shop.index') }}">
            <span class="brand-mark">MK</span>
            <span>{{ __('shop.brand.shop_name') }}</span>
        </a>
        <nav class="public-nav" aria-label="{{ __('shop.nav.breadcrumb') }}">
            <a class="public-nav-link" href="{{ route('shop.index') }}">{{ __('shop.nav.shop') }}</a>
            @if($productCategoryName && $productCategoryUrl)
                <a class="public-nav-link" href="{{ $productCategoryUrl }}">{{ $productCategoryName }}</a>
            @elseif($productCategoryName)
                <span class="public-nav-link">{{ $productCategoryName }}</span>
            @endif
        </nav>
    </div>
</header>

<main class="page">
    <section class="container page-section stack" style="gap: 2rem;">
        <div class="product-detail-layout card">
            <article class="product-detail-media">
                <div class="product-detail-main-frame">
                    @if($activeImage)
                        <img
                            src="{{ $activeImage }}"
                            alt="{{ $product->name }}"
                            class="product-detail-main-image"
                            data-product-gallery-main
                        >
                    @else
                        <div class="product-detail-placeholder">
                            {{ \Illuminate\Support\Str::of($product->name)->substr(0, 2)->upper() }}
                        </div>
                    @endif
                </div>

                @if(count($galleryImages) > 1)
                    <div class="product-detail-thumbnails" aria-label="{{ __('shop.product.gallery_title') }}">
                        @foreach($galleryImages as $index => $galleryImageUrl)
                            <button
                                type="button"
                                class="product-detail-thumb @if($index === 0) is-active @endif"
                                data-product-gallery-thumb
                                data-image="{{ $galleryImageUrl }}"
                            >
                                <img
                                    src="{{ $galleryImageUrl }}"
                                    alt="{{ $product->name }} {{ $index + 1 }}"
                                >
                            </button>
                        @endforeach
                    </div>
                @endif
            </article>

            <article class="product-detail-summary stack">
                <p class="small">
                    {{ __('shop.product.category') }}:
                    @if($productCategoryName && $productCategoryUrl)
                        <a class="product-detail-category" href="{{ $productCategoryUrl }}">{{ $productCategoryName }}</a>
                    @elseif($productCategoryName)
                        <span class="product-detail-category">{{ $productCategoryName }}</span>
                    @else
                        <span class="product-detail-category">-</span>
                    @endif
                </p>

                <h1 class="product-detail-title">{{ $product->name }}</h1>

                <div class="product-detail-price-wrap">
                    <div class="product-detail-price-row">
                        @if($product->sale_price)
                            <strong class="product-detail-price">{{ __('shop.product.price_mad', ['price' => number_format((float) $product->sale_price, 2)]) }}</strong>
                            <span class="product-detail-price-old">{{ __('shop.product.price_mad', ['price' => number_format((float) $product->price, 2)]) }}</span>
                        @else
                            <strong class="product-detail-price">{{ __('shop.product.price_mad', ['price' => number_format((float) $product->price, 2)]) }}</strong>
                        @endif
                    </div>

                    <div class="row" style="gap: 0.5rem;">
                        @if($product->is_featured)
                            <span class="badge badge-info">{{ __('shop.product.featured') }}</span>
                        @endif
                        @if($product->is_best_seller)
                            <span class="badge badge-success">{{ __('shop.product.best_seller') }}</span>
                        @endif
                        @if($product->stock <= 0)
                            <span class="badge badge-danger">{{ __('shop.product.out_of_stock') }}</span>
                        @else
                            <span class="badge badge-warning">{{ __('shop.product.in_stock_badge', ['count' => $product->stock]) }}</span>
                        @endif
                    </div>
                </div>

                <div class="product-detail-rating">
                    <div class="product-rating-stars" aria-hidden="true">
                        <span class="@if(($ratingAverage ?? 0) >= 1) is-filled @endif">&#9733;</span>
                        <span class="@if(($ratingAverage ?? 0) >= 2) is-filled @endif">&#9733;</span>
                        <span class="@if(($ratingAverage ?? 0) >= 3) is-filled @endif">&#9733;</span>
                        <span class="@if(($ratingAverage ?? 0) >= 4) is-filled @endif">&#9733;</span>
                        <span class="@if(($ratingAverage ?? 0) >= 5) is-filled @endif">&#9733;</span>
                    </div>
                    <p class="small">
                        @if($ratingAverage && $reviewsCount > 0)
                            {{ __('shop.product.rating_label', ['rating' => $ratingAverage]) }}
                            <span aria-hidden="true">&middot;</span>
                            {{ __('shop.product.rating_count', ['count' => $reviewsCount]) }}
                        @else
                            {{ __('shop.product.rating_fallback') }}
                        @endif
                    </p>
                </div>

                @if($specifications !== [])
                    <section class="product-detail-spec-grid">
                        @foreach($specifications as $specification)
                            <div class="product-spec-card">
                                <span class="product-spec-label">{{ $specification['label'] }}</span>
                                <strong class="product-spec-value">{{ $specification['value'] }}</strong>
                            </div>
                        @endforeach
                    </section>
                @endif

                @if($product->display_fabric_type || $product->display_fabric_country || $product->display_fabric_description || $product->fabric_image_url)
                    <section class="product-detail-fabric card stack">
                        <div class="row" style="justify-content: space-between; align-items: center;">
                            <h2 class="title product-detail-section-title">{{ __('shop.product.fabric_title') }}</h2>
                            @if($product->fabric_image_url)
                                <a
                                    class="ui-btn ui-btn--sm"
                                    href="{{ $product->fabric_image_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    {{ __('shop.product.view_fabric_image') }}
                                </a>
                            @endif
                        </div>

                        <div class="grid grid-2">
                            <div class="product-spec-card">
                                <span class="product-spec-label">{{ __('shop.product.fabric_type') }}</span>
                                <strong class="product-spec-value">{{ $product->display_fabric_type ?: '-' }}</strong>
                            </div>
                            <div class="product-spec-card">
                                <span class="product-spec-label">{{ __('shop.product.fabric_country') }}</span>
                                <strong class="product-spec-value">{{ $product->display_fabric_country ?: '-' }}</strong>
                            </div>
                        </div>

                        @if($product->display_fabric_description)
                            <p class="small">{{ $product->display_fabric_description }}</p>
                        @endif
                    </section>
                @endif

                <section class="product-detail-section">
                    <h2 class="title product-detail-section-title">{{ __('shop.product.details_title') }}</h2>
                    <p class="small product-detail-text">{{ $product->short_description ?: __('shop.product.details_placeholder') }}</p>
                </section>

                <section class="product-detail-section">
                    <h2 class="title product-detail-section-title">{{ __('shop.product.description_title') }}</h2>
                    <p class="small product-detail-text">{{ $product->description ?: __('shop.product.description_placeholder') }}</p>
                </section>

                <div class="actions">
                    <a class="ui-btn ui-btn--primary" href="{{ $orderNowUrl }}">{{ __('shop.actions.order_from_app') }}</a>
                    <a class="ui-btn" href="{{ route('shop.index') }}">{{ __('shop.actions.back_to_shop') }}</a>
                </div>
            </article>
        </div>

        <section class="stack">
            <div class="ui-section-header">
                <p class="ui-section-eyebrow">{{ __('shop.sections.similar_products_eyebrow') }}</p>
                <h2 class="ui-section-title">{{ __('shop.sections.similar_products_title') }}</h2>
                <p class="ui-section-description">{{ __('shop.sections.similar_products_description') }}</p>
            </div>

            @if($similarProducts->isEmpty())
                @include('shop.partials.empty-state', [
                    'title' => __('shop.empty.no_similar_products_title'),
                    'message' => __('shop.empty.no_similar_products_message'),
                ])
            @else
                <div class="storefront-product-grid">
                    @foreach($similarProducts as $similarProduct)
                        @include('shop.partials.product-card', ['product' => $similarProduct])
                    @endforeach
                </div>
            @endif
        </section>
    </section>
</main>

<script>
    document.querySelectorAll('[data-product-gallery-thumb]').forEach((button) => {
        button.addEventListener('click', () => {
            const mainImage = document.querySelector('[data-product-gallery-main]');

            if (!mainImage) {
                return;
            }

            mainImage.src = button.dataset.image || mainImage.src;

            document.querySelectorAll('[data-product-gallery-thumb]').forEach((thumb) => {
                thumb.classList.remove('is-active');
            });

            button.classList.add('is-active');
        });
    });
</script>
</body>
</html>
