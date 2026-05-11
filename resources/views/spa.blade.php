<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('shop.meta.web_client_title') }}</title>
    <meta name="robots" content="noindex,nofollow">
    <script>
        window.__MAKASOUK__ = {
            locale: @js(app()->getLocale()),
            direction: @js(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'),
        };
    </script>
    @vite(['resources/css/app.css', 'resources/js/main.js'])
</head>
<body>
<div id="app"></div>
</body>
</html>
