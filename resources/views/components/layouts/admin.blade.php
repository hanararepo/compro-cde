<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    @php
        $siteName     = $settings['site_name'] ?? \App\Models\Setting::get('site_name', config('app.name', 'CMS'));
        $siteFavicon  = $settings['site_favicon'] ?? \App\Models\Setting::get('site_favicon', '');
        $accentColor  = \App\Models\Setting::get('admin_accent_color', 'emerald');
        $accentShades = \App\Services\Setting\SettingService::getAccentShades($accentColor);
    @endphp

    <title>{{ $title ?? 'Admin Dashboard' }} - {{ $siteName }}</title>

    @if($siteFavicon)
        <link rel="icon" type="image/x-icon" href="{{ $siteFavicon }}">
        <link rel="shortcut icon" href="{{ $siteFavicon }}">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Dynamic Admin Dashboard Theme (Global System Setting) -->
    <style>
        :root {
            @foreach($accentShades as $shade => $hex)
            --color-brand-{{ $shade }}: {{ $hex }};
            @endforeach
        }
    </style>
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-100 flex overflow-hidden">
    
    <!-- Sidebar Partial -->
    @include('partials.sidebar')

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        
        <!-- Top Navbar Partial -->
        @include('partials.navbar')

        <!-- Page Content -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-50">
            <!-- Flash Alert -->
            <x-alert />

            <!-- Dynamic Slot -->
            {{ $slot }}
        </main>

        <!-- Footer Partial -->
        @include('partials.footer')
    </div>

    @stack('scripts')
</body>
</html>
