<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('shop.meta.public_title') }}</title>
    <meta name="description" content="{{ __('shop.meta.public_description') }}">
    <meta name="robots" content="index,follow">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ __('shop.meta.public_title') }}">
    <meta property="og:description" content="{{ __('shop.meta.public_description') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ __('shop.meta.public_title') }}">
    <meta name="twitter:description" content="{{ __('shop.meta.public_description') }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <script>
        window.__MAKASOUK__ = {
            locale: @js(app()->getLocale()),
            direction: @js(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'),
        };
    </script>
    @vite(['resources/css/app.css', 'resources/js/public/main.js'])
</head>
<body>
<div id="public-site"></div>
</body>
</html>
