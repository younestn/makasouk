<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->localizedTitle() }} | {{ __('shop.brand.shop_name') }}</title>
    <meta name="description" content="{{ $page->localizedExcerpt() ?: $page->localizedTitle() }}">
    @vite(['resources/css/app.css'])
</head>
<body>
<header class="public-header">
    <div class="container public-header-inner">
        <a class="brand" href="{{ url('/') }}">
            <span class="brand-mark">MK</span>
            <span>{{ __('shop.brand.shop_name') }}</span>
        </a>
        <nav class="public-nav" aria-label="{{ __('shop.nav.breadcrumb') }}">
            <a class="public-nav-link" href="{{ url('/') }}">{{ __('shop.nav.home') }}</a>
            <a class="public-nav-link public-nav-link--shop" href="{{ route('shop.index') }}">{{ __('shop.nav.shop') }}</a>
            <a class="public-nav-link" href="{{ url('/contact') }}">{{ __('shop.footer.contact') }}</a>
        </nav>
    </div>
</header>

<main class="page">
    <section class="container page-section">
        <article class="content-page-card">
            <p class="premium-eyebrow">{{ __('shop.content.legal_badge') }}</p>
            <h1 class="hero-title">{{ $page->localizedTitle() }}</h1>
            @if($page->localizedExcerpt())
                <p class="hero-subtitle">{{ $page->localizedExcerpt() }}</p>
            @endif

            <div class="content-page-body">
                {!! $page->localizedBody() ?: '<p>'.e(__('shop.content.empty_body')).'</p>' !!}
            </div>
        </article>
    </section>
</main>
</body>
</html>
