@php
    $siteName    = $settings['site_name']    ?? config('app.name');
    $siteLogo    = $settings['site_logo']    ?? '';
    $sitePhone   = trim($settings['contact_phone']   ?? '');
    $siteEmail   = trim($settings['contact_email']   ?? '');
    $siteAddress = trim($settings['contact_address'] ?? '');
    $siteCity    = trim($settings['contact_city']    ?? '');
    $siteHours   = trim($settings['contact_hours']   ?? '');
    $siteTagline = trim($settings['site_tagline']    ?? '');

    $footerSocialLinks = [];
    foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn', 'youtube' => 'YouTube'] as $platform => $label) {
        $url = trim($settings['social_' . $platform] ?? '');
        if ($url !== '') {
            $footerSocialLinks[] = ['url' => $url, 'label' => $label, 'icon' => 'fa-' . $platform];
        }
    }
@endphp

<!-- Company Footer -->
<footer class="footer-section company-footer overflow-hidden">
    <div class="container container-2">
        <div class="row footer-wrap">

            {{-- Col 1: Logo & Address --}}
            <div class="col-lg-3 col-md-6">
                <div class="footer-widget">
                    <div class="widget-header">
                        <div class="footer-logo">
                            <a href="{{ route('home') }}">
                                @if($siteLogo)
                                    <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="max-height: 48px;">
                                @else
                                    <img src="{{ asset('assets/img/logo/logo-2.png') }}" alt="{{ $siteName }}">
                                @endif
                            </a>
                        </div>
                    </div>
                    @if($siteTagline)
                        <p class="mb-10 text-white-50">{{ $siteTagline }}</p>
                    @endif
                    @if($siteAddress)
                        <p class="footer-location mb-0 text-white-50">{{ $siteAddress }}</p>
                    @endif
                    @if($siteCity)
                        <p class="mb-0 text-white-50">{{ $siteCity }}</p>
                    @endif
                </div>
            </div>

            {{-- Col 2: Quick Links --}}
            <div class="col-lg-3 col-md-6">
                <div class="footer-widget footer-col-2">
                    <h4 class="text-white mb-20" style="font-size: 18px;">{{ __('Quick Links') }}</h4>
                    <ul class="footer-list">
                        <li><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                        <li><a href="{{ route('about.awards-certificates') }}">{{ __('Awards & Certification') }}</a></li>
                        <li><a href="{{ route('about.photo-gallery') }}">{{ __('Gallery') }}</a></li>
                        <li><a href="{{ route('careers.index') }}">{{ __('Careers') }}</a></li>
                        <li><a href="{{ route('contact') }}">{{ __('Contact Us') }}</a></li>
                    </ul>
                </div>
            </div>

            {{-- Col 3: Coal Products --}}
            <div class="col-lg-3 col-md-6">
                <div class="footer-widget footer-col-2">
                    <h4 class="text-white mb-20" style="font-size: 18px;">{{ __('Coal Products') }}</h4>
                    <ul class="footer-list">
                        @foreach($footerCoalProducts ?? [] as $product)
                            <li>
                                <a href="{{ route('coal-products.show', $product->slug) }}">{{ $product->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Col 4: Contact --}}
            @if($sitePhone || $siteEmail || $siteHours || $footerSocialLinks)
            <div class="col-lg-3 col-md-6">
                <div class="footer-widget">
                    <h4 class="text-white mb-20" style="font-size: 18px;">{{ __('Contact Us') }}</h4>
                    <div class="footer-address">
                        @if($sitePhone)
                            <a class="number" href="tel:{{ preg_replace('/[^0-9+]/', '', $sitePhone) }}">{{ $sitePhone }}</a>
                        @endif
                        @if($siteEmail)
                            <a class="mail" href="mailto:{{ $siteEmail }}">{{ $siteEmail }}</a>
                        @endif
                        @if($siteHours)
                            <p class="mt-20 mb-0 text-white-50">{{ $siteHours }}</p>
                        @endif
                        @if($footerSocialLinks)
                            <ul class="social-list">
                                @foreach($footerSocialLinks as $social)
                                    <li>
                                        <a href="{{ $social['url'] }}" aria-label="{{ $social['label'] }}" target="_blank" rel="noopener noreferrer">
                                            <i class="fab {{ $social['icon'] }}" aria-hidden="true"></i>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>

    <div class="copyright-area">
        <div class="container">
            <div class="copyright-content text-center">
                <p>&copy; {{ date('Y') }} {{ $siteName }}. All Rights Reserved.</p>
            </div>
        </div>
    </div>
</footer>
