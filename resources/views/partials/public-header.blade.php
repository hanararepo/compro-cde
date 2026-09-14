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
            <button class="mobile-side-menu-close"><i class="fa-regular fa-xmark"></i></button>
        </div>
        <div class="side-menu-wrap">
            <ul class="mobile-nav">
                {{-- Public navigation is copied from public-navbar by Antra's mobile menu. --}}
                @auth
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                @else
                    <li><a href="{{ route('login') }}">Sign In</a></li>
                @endauth
            </ul>
        </div>
    </div>
</div>
<div class="mobile-side-menu-overlay"></div>
