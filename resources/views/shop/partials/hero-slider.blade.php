@if($banners->isEmpty())
    @include('shop.partials.empty-state', [
        'title' => __('shop.empty.no_hero_title'),
        'message' => __('shop.empty.no_hero_message'),
    ])
@else
    <section class="shop-hero" data-shop-hero data-autoplay="{{ $settings->hero_autoplay ? '1' : '0' }}" data-delay="{{ $settings->hero_autoplay_delay_ms }}">
        <div class="shop-hero-track" data-shop-hero-track>
            @foreach($banners as $index => $banner)
                <article class="shop-hero-slide {{ $index === 0 ? 'is-active' : '' }}" data-shop-hero-slide>
                    <div class="shop-hero-media">
                        @php
                            $bannerImage = \Illuminate\Support\Str::startsWith($banner->image_path, ['http://', 'https://', '/'])
                                ? $banner->image_path
                                : \Illuminate\Support\Facades\Storage::url($banner->image_path);
                        @endphp
                        <img src="{{ $bannerImage }}" alt="{{ $banner->title }}" loading="lazy">
                    </div>
                    <div class="shop-hero-content">
                        @if($banner->badge)
                            <span class="badge badge-warning">{{ $banner->badge }}</span>
                        @endif
                        <h1>{{ $banner->title }}</h1>
                        @if($banner->subtitle)
                            <p>{{ $banner->subtitle }}</p>
                        @endif
                        @if($banner->button_link)
                            <a class="ui-btn ui-btn--primary" href="{{ $banner->button_link }}">{{ $banner->button_text ?: __('shop.actions.explore') }}</a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <button type="button" class="shop-hero-nav shop-hero-prev" data-shop-hero-prev aria-label="{{ __('shop.pagination.previous') }}">&#8249;</button>
        <button type="button" class="shop-hero-nav shop-hero-next" data-shop-hero-next aria-label="{{ __('shop.pagination.next') }}">&#8250;</button>

        <div class="shop-hero-dots">
            @foreach($banners as $index => $banner)
                <button type="button" class="shop-hero-dot {{ $index === 0 ? 'is-active' : '' }}" data-shop-hero-dot="{{ $index }}" aria-label="{{ __('shop.actions.go_to_banner', ['index' => $index + 1]) }}"></button>
            @endforeach
        </div>
    </section>
@endif

