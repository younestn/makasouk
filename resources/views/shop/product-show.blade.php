<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $product->name }} | {{ __('shop.brand.shop_name') }}</title>
    <meta name="description" content="{{ $product->short_description ?: \Illuminate\Support\Str::limit($product->description, 140) }}">
    @vite(['resources/css/app.css'])
</head>
<body>
<header class="public-header">
    <div class="container public-header-inner">
        <a class="brand" href="{{ route('shop.index') }}">
            <span class="brand-mark">MK</span>
            <span>{{ __('shop.brand.shop_name') }}</span>
        </a>
        <nav class="public-nav" aria-label="{{ __('shop.nav.breadcrumb') }}">
            <a class="public-nav-link" href="{{ route('shop.index') }}">{{ __('shop.nav.shop') }}</a>
            <a class="public-nav-link" href="{{ route('shop.category', $product->category->slug) }}">{{ $product->category->name }}</a>
        </nav>
    </div>
</header>

<main class="page">
    <section class="container page-section">
        <div class="grid grid-2">
            <article class="card">
                @if($product->main_image)
                    @php
                        $mainImage = \Illuminate\Support\Str::startsWith($product->main_image, ['http://', 'https://', '/'])
                            ? $product->main_image
                            : \Illuminate\Support\Facades\Storage::url($product->main_image);
                    @endphp
                    <img src="{{ $mainImage }}" alt="{{ $product->name }}" style="width: 100%; border-radius: 12px; object-fit: cover; max-height: 460px;">
                @else
                    <div class="shop-category-placeholder" style="height: 360px;">{{ \Illuminate\Support\Str::of($product->name)->substr(0, 2)->upper() }}</div>
                @endif
            </article>

            <article class="card stack">
                <p class="small">{{ __('shop.product.category') }}: <a href="{{ route('shop.category', $product->category->slug) }}">{{ $product->category->name }}</a></p>
                <h1 class="hero-title" style="font-size: clamp(1.6rem, 3vw, 2.4rem);">{{ $product->name }}</h1>
                <p class="subtitle">{{ $product->short_description ?: __('shop.product.tailoring_placeholder') }}</p>

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

                <div class="row" style="align-items: baseline; gap: 0.6rem;">
                    @if($product->sale_price)
                        <strong style="font-size: 1.7rem;">{{ __('shop.product.price_mad', ['price' => number_format((float) $product->sale_price, 2)]) }}</strong>
                        <span class="small" style="text-decoration: line-through;">{{ __('shop.product.price_mad', ['price' => number_format((float) $product->price, 2)]) }}</span>
                    @else
                        <strong style="font-size: 1.7rem;">{{ __('shop.product.price_mad', ['price' => number_format((float) $product->price, 2)]) }}</strong>
                    @endif
                </div>

                <p>{{ $product->description ?: __('shop.product.description_placeholder') }}</p>

                @if($product->display_fabric_type || $product->display_fabric_country || $product->display_fabric_description || $product->fabric_image_url)
                    <section class="card stack" style="padding: 1rem; border: 1px solid rgba(148, 163, 184, 0.25);">
                        <h2 class="title" style="font-size: 1.1rem; margin: 0;">{{ __('shop.product.fabric_title') }}</h2>

                        <div class="grid grid-2">
                            <p class="small" style="margin: 0;">
                                <strong>{{ __('shop.product.fabric_type') }}:</strong> {{ $product->display_fabric_type ?: '-' }}
                            </p>
                            <p class="small" style="margin: 0;">
                                <strong>{{ __('shop.product.fabric_country') }}:</strong> {{ $product->display_fabric_country ?: '-' }}
                            </p>
                        </div>

                        @if($product->display_fabric_description)
                            <p class="small" style="margin: 0;">{{ $product->display_fabric_description }}</p>
                        @endif

                        @if($product->fabric_image_url)
                            <div class="stack" style="gap: 0.45rem;">
                                <a
                                    class="ui-btn"
                                    href="{{ $product->fabric_image_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    style="width: fit-content;"
                                >
                                    {{ __('shop.product.view_fabric_image') }}
                                </a>
                                <img
                                    src="{{ $product->fabric_image_url }}"
                                    alt="{{ __('shop.product.fabric_image_alt', ['name' => $product->name]) }}"
                                    style="max-width: 260px; border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.25); object-fit: cover;"
                                >
                            </div>
                        @endif
                    </section>
                @endif

                <div class="actions">
                    <a class="ui-btn ui-btn--primary" href="{{ url('/app/login') }}">{{ __('shop.actions.order_from_app') }}</a>
                    <a class="ui-btn" href="{{ route('shop.index') }}">{{ __('shop.actions.back_to_shop') }}</a>
                </div>
            </article>
        </div>
    </section>
</main>
</body>
</html>
