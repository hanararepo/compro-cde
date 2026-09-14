<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    @php
        $siteName    = $settings['site_name'] ?? \App\Models\Setting::get('site_name', config('app.name', 'CMS'));
        $siteFavicon = $settings['site_favicon'] ?? \App\Models\Setting::get('site_favicon', '');
    @endphp

    <title>{{ $title ?? 'Login' }} - {{ $siteName }}</title>

    @if($siteFavicon)
        <link rel="icon" type="image/x-icon" href="{{ $siteFavicon }}">
        <link rel="shortcut icon" href="{{ $siteFavicon }}">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased bg-gradient-to-br from-slate-50 via-emerald-50/40 to-white flex items-center justify-center p-4">
    {{ $slot }}

    @stack('scripts')
</body>
</html>
