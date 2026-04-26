<article class="shop-product-card card">
    <a class="shop-product-media" href="{{ route('shop.product.show', $product->slug) }}">
        @if($product->main_image)
            @php
                $productImage = \Illuminate\Support\Str::startsWith($product->main_image, ['http://', 'https://', '/'])
                    ? $product->main_image
                    : \Illuminate\Support\Facades\Storage::url($product->main_image);
            @endphp
            <img
                src="{{ $productImage }}"
                alt="{{ $product->name }}"
                loading="lazy"
            >
        @else
            <div class="shop-category-placeholder">{{ \Illuminate\Support\Str::of($product->name)->substr(0, 2)->upper() }}</div>
        @endif

        <div class="shop-product-badges">
            @if($product->is_featured)
                <span class="badge badge-info">{{ __('shop.product.featured') }}</span>
            @endif
            @if($product->is_best_seller)
                <span class="badge badge-success">{{ __('shop.product.best_seller') }}</span>
            @endif
            @if($product->published_at && $product->published_at->greaterThan(now()->subDays(14)))
                <span class="badge badge-warning">{{ __('shop.product.new') }}</span>
            @endif
            @if($product->stock <= 0)
                <span class="badge badge-danger">{{ __('shop.product.out_of_stock') }}</span>
            @endif
        </div>
    </a>

    <div class="stack">
        <p class="small">{{ $product->category?->name }}</p>
        <a class="shop-product-name" href="{{ route('shop.product.show', $product->slug) }}">{{ $product->name }}</a>
        <p class="small">{{ \Illuminate\Support\Str::limit($product->short_description ?: $product->description, 95) }}</p>

        <div class="row" style="justify-content: space-between;">
            <div class="row" style="gap:0.45rem;">
                @if($product->sale_price)
                    <strong>{{ __('shop.product.price_mad', ['price' => number_format((float) $product->sale_price, 2)]) }}</strong>
                    <span class="small" style="text-decoration: line-through;">{{ __('shop.product.price_mad', ['price' => number_format((float) $product->price, 2)]) }}</span>
                @else
                    <strong>{{ __('shop.product.price_mad', ['price' => number_format((float) $product->price, 2)]) }}</strong>
                @endif
            </div>
            <span class="small">{{ __('shop.product.stock', ['count' => $product->stock]) }}</span>
        </div>

        <a class="ui-btn ui-btn--primary ui-btn--sm" href="{{ route('shop.product.show', $product->slug) }}">{{ __('shop.product.view_product') }}</a>
    </div>
</article>

