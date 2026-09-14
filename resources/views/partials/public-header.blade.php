@php
    $siteName  = $settings['site_name'] ?? \App\Models\Setting::get('site_name', config('app.name', 'Antra'));
    $siteLogo  = $settings['site_logo'] ?? \App\Models\Setting::get('site_logo', '');
    $sitePhone = $settings['contact_phone'] ?? '';
    $siteEmail = $settings['contact_email'] ?? '';
@endphp

<!-- Main Header -->
<header class="header sticky-active">
    {{-- Non-home pages: apply 'fixed' immediately so background appears on first paint,
         without waiting for JS. 'no-sticky-anim' suppresses the slide-down animation
         that would otherwise play on initial render. --}}
    <div class="primary-header {{ !request()->routeIs('home') ? 'fixed no-sticky-anim' : '' }}">
        <div class="container">
            <div class="primary-header-inner">
                <div class="header-left-wrap">
                    <div class="header-logo d-lg-block">
                        <a href="{{ route('home') }}">
                            @if($siteLogo)
                                <img src="{{ $siteLogo }}" alt="{{ $siteName }}" height="50" loading="eager">
                            @else
                                <img src="{{ asset('assets/img/logo/logo-2.png') }}" alt="{{ $siteName }}" height="50" loading="eager">
                            @endif
                        </a>
                    </div>
                    <!-- Navigation Links Partial -->
                    @include('partials.public-navbar')
                </div>

                <div class="header-right-wrap">
                    <nav class="header-language-switcher" aria-label="{{ __('Language') }}">
                        @foreach (['id' => 'Bahasa Indonesia', 'en' => 'English'] as $locale => $languageName)
                            <a href="{{ route('locale.switch', $locale) }}"
                               lang="{{ $locale }}"
                               hreflang="{{ $locale }}"
                               aria-label="{{ $languageName }}"
                               @if (app()->getLocale() === $locale) aria-current="true" @endif>{{ strtoupper($locale) }}</a>
                            @if (!$loop->last)
                                <span aria-hidden="true">/</span>
                            @endif
                        @endforeach
                    </nav>

                    <button class="mobile-side-menu-toggle" type="button" aria-label="{{ __('Toggle navigation') }}">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Popup Search Box -->
<div id="popup-search-box">
    <div class="box-inner-wrap d-flex align-items-center">
        <form id="form" action="{{ route('news.index') }}" method="get" role="search">
            <input id="popup-search" type="text" name="search" placeholder="Type keywords here...">
        </form>
        <div class="search-close"><i class="fa-sharp fa-regular fa-xmark"></i></div>
    </div>
</div>

<!-- Mobile Side Menu -->
<div class="mobile-side-menu">
    <div class="side-menu-content">
        <div class="side-menu-head">
            <a href="{{ route('home') }}">
                @if($siteLogo)
                    <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="max-height: 40px;">
                @else
                    <img src="{{ asset('assets/img/logo/logo-2.png') }}" alt="{{ $siteName }}">
                @endif
            </a>
            <button class="mobile-side-menu-close" type="button" aria-label="{{ __('Close navigation') }}">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="side-menu-wrap"></div>
    </div>
</div>
<div class="mobile-side-menu-overlay"></div>
