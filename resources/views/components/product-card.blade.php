@props(['product'])

@php
    $productUrl = route('shop.product.show', $product->slug);
    $productImage = $product->main_image_url;
    $category = $product->category;
    $categoryName = $category?->display_name;
    $categoryUrl = $category ? route('shop.category', $category->slug) : null;
    $stockCount = (int) ($product->stock ?? 0);
    $isOutOfStock = $stockCount <= 0;
    $isNew = $product->published_at?->greaterThan(now()->subDays(14)) ?? false;
    $hasSalePrice = filled($product->sale_price) && (float) $product->sale_price < (float) $product->price;
    $currentPrice = $hasSalePrice ? (float) $product->sale_price : (float) $product->price;
    $originalPrice = (float) $product->price;
    $discountPercent = $hasSalePrice && $originalPrice > 0
        ? (int) round((($originalPrice - $currentPrice) / $originalPrice) * 100)
        : null;
    $ratingAverage = $product->reviews_avg_rating !== null ? round((float) $product->reviews_avg_rating, 1) : null;
    $reviewsCount = (int) ($product->reviews_count ?? 0);
    $ratingFill = $ratingAverage !== null ? max(0, min(100, ($ratingAverage / 5) * 100)) : 0;
    $summary = trim((string) ($product->short_description ?: $product->description ?: ''));
    $summary = $summary !== '' ? \Illuminate\Support\Str::limit($summary, 96) : __('shop.product.tailoring_placeholder');
    $placeholderParts = collect(preg_split('/\s+/u', trim((string) $product->name)) ?: [])
        ->filter()
        ->take(2)
        ->map(fn (string $segment): string => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($segment, 0, 1)));
    $placeholder = $placeholderParts->implode('');
    $placeholder = $placeholder !== ''
        ? $placeholder
        : \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr((string) $product->name, 0, 2));
@endphp

<article class="shop-product-card">
    <a
        class="shop-product-media"
        href="{{ $productUrl }}"
        aria-label="{{ __('shop.actions.view_product') }}: {{ $product->name }}"
    >
        @if($productImage)
            <img
                src="{{ $productImage }}"
                alt="{{ $product->name }}"
                loading="lazy"
                decoding="async"
            >
        @else
            <div class="shop-product-placeholder" aria-hidden="true">{{ $placeholder }}</div>
        @endif

        <div class="shop-product-badges">
            @if($discountPercent)
                <span class="badge badge-danger text-bg-danger shop-product-badge shop-product-badge--discount">-{{ $discountPercent }}%</span>
            @endif
            @if($product->is_featured)
                <span class="badge badge-info text-bg-info shop-product-badge">{{ __('shop.product.featured') }}</span>
            @endif
            @if($product->is_best_seller)
                <span class="badge badge-success text-bg-success shop-product-badge">{{ __('shop.product.best_seller') }}</span>
            @endif
            @if($isNew)
                <span class="badge badge-warning text-bg-warning shop-product-badge">{{ __('shop.product.new') }}</span>
            @endif
            @if($isOutOfStock)
                <span class="badge badge-danger text-bg-danger shop-product-badge">{{ __('shop.product.out_of_stock') }}</span>
            @endif
        </div>
    </a>

    <div class="shop-product-card__body">
        <div class="shop-product-card__header">
            @if($categoryName && $categoryUrl)
                <a class="shop-product-category" href="{{ $categoryUrl }}">{{ $categoryName }}</a>
            @elseif($categoryName)
                <span class="shop-product-category">{{ $categoryName }}</span>
            @endif

            <div
                class="shop-product-rating"
                @if($ratingAverage !== null && $reviewsCount > 0)
                    aria-label="{{ __('shop.product.rating_label', ['rating' => $ratingAverage]) }}"
                @endif
            >
                <span class="shop-product-rating__stars" aria-hidden="true">
                    <span class="shop-product-rating__stars-base">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                    <span class="shop-product-rating__stars-fill" style="width: {{ $ratingFill }}%;">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                </span>

                @if($ratingAverage !== null && $reviewsCount > 0)
                    <span class="shop-product-rating__value">{{ $ratingAverage }}</span>
                    <span class="shop-product-rating__count">({{ $reviewsCount }})</span>
                @else
                    <span class="shop-product-rating__empty">{{ __('shop.product.rating_fallback') }}</span>
                @endif
            </div>
        </div>

        <div class="shop-product-card__content">
            <a class="shop-product-name" href="{{ $productUrl }}" title="{{ $product->name }}">{{ $product->name }}</a>
            <p class="small shop-product-summary">{{ $summary }}</p>
        </div>

        <div class="shop-product-price-row">
            <div class="shop-product-price-block">
                <strong class="shop-product-price-current">{{ __('shop.product.price_mad', ['price' => number_format($currentPrice, 2)]) }}</strong>

                @if($hasSalePrice)
                    <span class="small shop-product-price-old">{{ __('shop.product.price_mad', ['price' => number_format($originalPrice, 2)]) }}</span>
                @endif
            </div>

            <span class="badge {{ $isOutOfStock ? 'badge-danger text-bg-danger' : 'badge-neutral text-bg-secondary' }} shop-product-stock">
                {{ $isOutOfStock ? __('shop.product.out_of_stock') : __('shop.product.in_stock_badge', ['count' => $stockCount]) }}
            </span>
        </div>

        <div class="shop-product-card__actions">
            <a class="ui-btn ui-btn--primary ui-btn--sm ui-btn--block" href="{{ $productUrl }}">
                {{ __('shop.product.view_product') }}
            </a>
        </div>
    </div>
</article>
