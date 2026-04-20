<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} | Public Site</title>
    <meta name="description" content="Makasouk connects customers and tailors through realtime order workflows and lifecycle clarity.">
    <meta name="robots" content="index,follow">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ config('app.name') }} | Public Site">
    <meta property="og:description" content="Makasouk connects customers and tailors through realtime order workflows and lifecycle clarity.">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ config('app.name') }} | Public Site">
    <meta name="twitter:description" content="Makasouk connects customers and tailors through realtime order workflows and lifecycle clarity.">
    <link rel="canonical" href="{{ url()->current() }}">
    @vite(['resources/css/app.css', 'resources/js/public/main.js'])
</head>
<body>
<div id="public-site"></div>
</body>
</html>
