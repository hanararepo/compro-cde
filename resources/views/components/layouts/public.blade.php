<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $siteName     = $settings['site_name'] ?? \App\Models\Setting::get('site_name', config('app.name'));
        $siteTagline  = $settings['site_tagline'] ?? \App\Models\Setting::get('site_tagline', 'Modern CMS');
        $siteFavicon  = $settings['site_favicon'] ?? \App\Models\Setting::get('site_favicon', '');
        $pageTitle    = isset($title) ? $title . ' — ' . $siteName : $siteName . ' — ' . $siteTagline;
        $pageDesc     = $metaDescription ?? ($settings['seo_meta_description'] ?? \App\Models\Setting::get('seo_meta_description', 'High performance modern CMS built with Laravel.'));
        $pageKeywords = $metaKeywords ?? ($settings['seo_meta_keywords'] ?? \App\Models\Setting::get('seo_meta_keywords', 'laravel, cms, articles, gallery'));
        $canonical    = $canonicalUrl ?? request()->url();
        $ogImage      = $ogImage ?? ($settings['og_image'] ?? \App\Models\Setting::get('og_image', ''));
        $ogType       = $ogType ?? 'website';
        $currentLocale = app()->getLocale();
        $altLocale     = $currentLocale === 'id' ? 'en' : 'id';
        // Build alternate URL for the other locale by temporarily switching locale in route
        $hreflangCurrent = $canonical;
        $hreflangAlt     = route('locale.switch', $altLocale);
        $noIndex         = $noIndex ?? false;
    @endphp

    @if($siteFavicon)
        <link rel="icon" type="image/x-icon" href="{{ $siteFavicon }}">
        <link rel="shortcut icon" href="{{ $siteFavicon }}">
    @else
        <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/favicon.png') }}">
    @endif

    <title>{{ $pageTitle }}</title>
    @if($noIndex)
        <meta name="robots" content="noindex, follow">
    @else
        <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    @endif
    <meta name="description" content="{{ $pageDesc }}">
    <meta name="keywords" content="{{ $pageKeywords }}">
    <link rel="canonical" href="{{ $canonical }}">

    {{-- hreflang for multilingual --}}
    <link rel="alternate" hreflang="{{ $currentLocale }}" href="{{ $hreflangCurrent }}">
    <link rel="alternate" hreflang="{{ $altLocale }}" href="{{ $hreflangAlt }}">
    <link rel="alternate" hreflang="x-default" href="{{ $hreflangCurrent }}">

    {{-- Open Graph --}}
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDesc }}">
    @if($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
    @endif
    <meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale()) }}">

    {{-- Article-specific Open Graph --}}
    @isset($articlePublishedTime)
        <meta property="article:published_time" content="{{ $articlePublishedTime }}">
    @endisset
    @isset($articleModifiedTime)
        <meta property="article:modified_time" content="{{ $articleModifiedTime }}">
    @endisset
    @isset($articleAuthor)
        <meta property="article:author" content="{{ $articleAuthor }}">
    @endisset
    @isset($articleSection)
        <meta property="article:section" content="{{ $articleSection }}">
    @endisset

    <!-- Google Fonts for Template -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cal+Sans&family=Golos+Text:wght@400..900&display=swap" rel="stylesheet">

    <!-- Compiled Template CSS via Vite -->
    @vite(['resources/css/public.css'])

    {{-- Per-page JSON-LD structured data injected by individual views --}}
    @stack('structured-data')

    {{-- Per-page extra head meta (e.g. noindex overrides) --}}
    @stack('head-meta')
</head>
<body>

    @if(request()->routeIs('home'))
        @include('partials.public-preloader')
    @endif

    <!-- Header Section -->
    @include('partials.public-header')

    <!-- Antra page structure; scrolling is handled natively by the browser. -->
    <div id="antra-smooth-wrapper">
        <div id="antra-smooth-content">
            <!-- Main Content Slot -->
            <main>
                {{ $slot }}
            </main>

            <!-- Footer Section -->
            @include('partials.public-company-footer')
        </div>
    </div>

    <!-- Scroll up button -->
    <div id="scroll-percentage"><span id="scroll-percentage-value"></span></div>

    <!-- Vendor JavaScripts (jQuery, GSAP, Swiper, etc.) -->
    <script src="{{ asset('assets/js/vendor/jquary-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendor/bootstrap-bundle.js') }}"></script>
    <script src="{{ asset('assets/js/vendor/imagesloaded-pkgd.js') }}"></script>
    <script src="{{ asset('assets/js/vendor/waypoints.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendor/venobox.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendor/odometer.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendor/meanmenu.js') }}"></script>
    <script src="{{ asset('assets/js/vendor/jquery.isotope.js') }}"></script>
    <script src="{{ asset('assets/js/vendor/swiper.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendor/split-type.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendor/gsap.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendor/scroll-trigger.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendor/jquery.carouselTicker.js') }}"></script>
    <script src="{{ asset('assets/js/vendor/nice-select.js') }}"></script>
    <script src="{{ asset('assets/js/vendor/jquery.event.move.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendor/jquery.twentytwenty.min.js') }}"></script>

    <!-- Public Vite Script (includes template main.js & Alpine.js) -->
    @vite(['resources/js/public.js'])

    @stack('scripts')
</body>
</html>
