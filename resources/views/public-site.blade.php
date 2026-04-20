<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} | Public Site</title>
    @vite(['resources/css/app.css', 'resources/js/public/main.js'])
</head>
<body>
<div id="public-site"></div>
</body>
</html>
