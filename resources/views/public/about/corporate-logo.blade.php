<x-layouts.public
    :title="__('Corporate Logo')"
    :metaDescription="__('View the official corporate logo of our company along with its usage guidelines and brand identity elements.')"
    :canonicalUrl="route('about.corporate-logo')"
>
    <div class="home-header-background" aria-hidden="true"></div>

    @php
        $siteLogo = $settings['site_logo'] ?? \App\Models\Setting::get('site_logo', asset('assets/img/logo/logo-2.png'));
        $siteName = $settings['site_name'] ?? \App\Models\Setting::get('site_name', config('app.name', 'PT Cakrawala Dinamika Energi'));
    @endphp

    <section class="corporate-logo-section pt-130 pb-130" aria-labelledby="corporate-logo-heading">
        <div class="container container-2">

            {{-- Section Heading (Consistent with Vision & Mission / Introduction) --}}
            <div class="row section-heading-wrap ml-0 mw-100 mb-50 slide-anim" data-scroll-repeat>
                <div class="shape">
                    <img src="{{ asset('assets/img/shapes/section-heading.png') }}" alt="" aria-hidden="true">
                </div>
                <div class="col-lg-4 col-md-12">
                    <div class="section-heading mb-0">
                        <h4 class="sub-heading">{{ __('About Us') }}</h4>
                    </div>
                </div>
                <div class="col-lg-8 col-md-12">
                    <div class="section-heading section-heading-2 mb-0">
                        @php
                            $corporateLogoTitle = __('Corporate Logo');
                            $titleWords = explode(' ', $corporateLogoTitle, 2);
                            $firstWord = $titleWords[0] ?? $corporateLogoTitle;
                            $secondWord = $titleWords[1] ?? '';
                        @endphp
                        <h1 id="corporate-logo-heading" class="section-title cursor-effect title-2">
                            {{ $firstWord }}@if($secondWord) <span>{{ $secondWord }}</span>@endif
                        </h1>
                    </div>
                </div>
            </div>

            {{-- Main Content Grid --}}
            <div class="row corporate-logo-grid g-5 align-items-stretch">
                {{-- Left: Logo Showcase Card (Using Logo From Settings) --}}
                <div class="col-lg-5">
                    <div class="corporate-logo-card-wrap h-100 slide-anim" data-scroll-repeat>
                        <div class="corporate-logo-card">
                            <div class="logo-card-badge">
                                <span class="badge-dot"></span>
                                <span>{{ __('Corporate Logo') }}</span>
                            </div>

                            <figure class="logo-display-frame">
                                @if(!empty($siteLogo))
                                    <img src="{{ $siteLogo }}"
                                         alt="{{ $siteName }}"
                                         class="corporate-logo-img">
                                @endif
                            </figure>

                            <div class="logo-card-footer">
                                <h2 class="logo-entity-name">{{ $siteName }}</h2>
                                <p class="logo-entity-desc">{{ __('Energy, Coal Mining & Sustainable Solutions') }}</p>

                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right: Philosophy & Explanation Cards --}}
                <div class="col-lg-7">
                    <div class="corporate-logo-philosophy-wrap d-flex flex-column gap-4 h-100">

                        {{-- Card 1: Tentang Logo (Icon CDE) --}}
                        <article class="logo-philosophy-card slide-anim" data-scroll-repeat data-delay="0.1">
                            <div class="philosophy-card-header">
                                <div class="philosophy-icon-wrap">
                                    <img src="{{ asset('assets/img/logo/cde-text.png') }}" alt="CDE" class="philosophy-thumb-cde">
                                </div>
                                <div>
                                    <span class="philosophy-badge">{{ __('Corporate Identity') }}</span>
                                    <h2 class="philosophy-title">{{ __('About Logo') }}</h2>
                                </div>
                            </div>
                            <div class="philosophy-card-body">
                                <p class="philosophy-text">
                                    {{ __('This logo represents the CDE Group, who have the competence to manage and operate coal mines effectively and sustainably') }}
                                </p>
                            </div>
                        </article>

                        {{-- Card 2: Bentuk Hexagon (Icon Hexagon) --}}
                        <article class="logo-philosophy-card slide-anim" data-scroll-repeat data-delay="0.2">
                            <div class="philosophy-card-header">
                                <div class="philosophy-icon-wrap">
                                    <img src="{{ asset('assets/img/logo/cde-hexagon.png') }}" alt="Hexagon" class="philosophy-thumb-hex">
                                </div>
                                <div>
                                    <span class="philosophy-badge">{{ __('Logo Philosophy') }}</span>
                                    <h2 class="philosophy-title">{{ __('Hexagon Shape') }}</h2>
                                </div>
                            </div>
                            <div class="philosophy-card-body">
                                <p class="philosophy-text">
                                    {{ __("It is the embodiment of a honeycomb storage form that is shaped like a hexagon. In this case, the honeycomb with its hexagonal shape provides effectiveness with great results maximum. This philosophy is a guideline for the CDE Group to always prioritize the values of People, Committed, Teamwork to meet the world's energy needs which are constantly increasing and always consistent to continue to grow to face competition in the future") }}
                                </p>
                            </div>
                        </article>

                    </div>
                </div>
            </div>

        </div>
    </section>
</x-layouts.public>
