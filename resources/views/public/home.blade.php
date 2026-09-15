@php
    $homeDesc = \App\Models\Setting::get('seo_meta_description',
        \App\Models\Setting::get('site_description', 'A high-performance, modern CMS platform.'));
    $homeKeywords = \App\Models\Setting::get('seo_meta_keywords', '');
    $siteName = \App\Models\Setting::get('site_name', config('app.name', 'Antra'));
    $siteDesc = \App\Models\Setting::get('site_description', 'Whether it\'s your home, office, or a commercial project, we are dedicated to bringing your vision to life with precision and creativity.');
    $siteUrl  = config('app.url');
    $siteLogo = \App\Models\Setting::get('site_logo', asset('assets/img/logo/logo-2.png'));
    $sitePhone = \App\Models\Setting::get('contact_phone', '');
    $siteEmail = \App\Models\Setting::get('contact_email', '');
    $siteAddress = \App\Models\Setting::get('contact_address', '');
@endphp

@push('structured-data')
@php
    $orgSchema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Organization',
        'name'        => $siteName,
        'url'         => $siteUrl,
        'logo'        => $siteLogo ?: asset('assets/img/logo/logo-2.png'),
        'description' => $homeDesc,
        'sameAs'      => [],
    ];
    if ($sitePhone) {
        $orgSchema['contactPoint'] = [
            '@type'       => 'ContactPoint',
            'telephone'   => $sitePhone,
            'contactType' => 'customer service',
        ];
    }
    if ($siteAddress) {
        $orgSchema['address'] = [
            '@type'           => 'PostalAddress',
            'streetAddress'   => $siteAddress,
        ];
    }
@endphp
<script type="application/ld+json">
{!! json_encode($orgSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_TAG) !!}
</script>
@endpush

<x-layouts.public :metaDescription="$homeDesc" :metaKeywords="$homeKeywords" :canonicalUrl="route('home')">

    <!-- Hero Slider Section -->
    @if ($sliders->isNotEmpty())
    <section class="slider-section home-hero overflow-hidden">
        <div class="antra-slider swiper-container">
            <div class="swiper-wrapper">
                @foreach ($sliders as $slider)
                <div class="swiper-slide" data-slider-id="{{ $slider->id }}">
                    <div class="slider-item">
                        <div class="home-hero-media" aria-hidden="true">
                            <picture>
                                @if ($slider->mobileImageUrl())
                                    <source media="(max-width: 767px)" srcset="{{ $slider->mobileImageUrl() }}">
                                @endif
                                <img src="{{ $slider->desktopImageUrl() }}"
                                     alt=""
                                     loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                     fetchpriority="{{ $loop->first ? 'high' : 'auto' }}"
                                     decoding="async">
                            </picture>
                        </div>
                        <div class="container slider-container">
                            <div class="slider-content-wrap">
                                <div class="slider-content">
                                    <div class="section-heading white-content">
                                        <h4 class="sub-heading">{{ $slider->title }}</h4>
                                        @if (filled($slider->description))
                                            <h2 class="section-title cursor-effect text-white">{{ $slider->description }}</h2>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @if ($sliders->count() > 1)
                <div class="antra-swiper-pagination home-hero-pagination"></div>
            @endif
        </div>
    </section>
    @else
        <div class="home-header-background" aria-hidden="true"></div>
    @endif
    <!-- ./ slider-section -->

    @if ($galleryVideos->isNotEmpty())
        <section class="home-video-section pt-150 pb-150 overflow-hidden tl-bg-color" aria-labelledby="home-video-heading">
            <div class="container container-2">
                <div class="row section-heading-wrap slide-anim" data-scroll-repeat>
                    <div class="shape"><img src="{{ asset('assets/img/shapes/section-heading.png') }}" alt="" aria-hidden="true"></div>
                    <div class="col-lg-4 col-md-12">
                        <div class="section-heading mb-0">
                            <h4 class="sub-heading">{{ __('Video Gallery') }}</h4>
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-12">
                        <div class="section-heading section-heading-2 mb-0">
                            <h2 id="home-video-heading" class="section-title cursor-effect title-2">{{ __('A Closer Look') }}<br> <span>{{ __('At Our Company') }}</span></h2>
                            <p class="home-video-description">{{ __('Meet our people, explore our operations, and discover the story behind our company. Take a closer look through our videos.') }}</p>
                        </div>
                    </div>
                </div>
                <div class="home-video-slider swiper-container slide-anim" data-scroll-repeat data-delay="0.12">
                    <div class="swiper-wrapper">
                        @foreach ($galleryVideos as $video)
                            <article class="video-section swiper-slide" data-gallery-video-id="{{ $video->id }}" aria-labelledby="home-video-title-{{ $video->id }}">
                                <div class="bg-img" aria-hidden="true">
                                    <img src="{{ $video->thumbnailUrl(highResolution: true) }}"
                                         data-video-poster
                                         data-fallback="{{ $video->thumbnailUrl() }}"
                                         alt="" width="1280" height="720" loading="lazy" decoding="async">
                                </div>
                                <div class="video-content">
                                    <div class="play-btn">
                                        <a class="video-popup"
                                           href="{{ $video->embedUrl() }}"
                                           data-vbtype="video"
                                           data-autoplay="true"
                                           data-gall="home-videos"
                                           data-maxwidth="1200px"
                                           aria-label="{{ __('Play video: :title', ['title' => $video->title]) }}">
                                            <i class="fa-solid fa-play" aria-hidden="true"></i>
                                        </a>
                                    </div>
                                    <div class="home-video-meta">
                                        <span class="home-video-watch">{{ __('Watch Video') }}</span>
                                        <h3 id="home-video-title-{{ $video->id }}" class="home-video-name">{{ $video->title }}</h3>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    @if ($galleryVideos->count() > 1)
                        <div class="home-video-navigation">
                            <button class="home-video-arrow home-video-prev" type="button" aria-label="{{ __('Previous video') }}">
                                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                            </button>
                            <div class="home-video-pagination"></div>
                            <button class="home-video-arrow home-video-next" type="button" aria-label="{{ __('Next video') }}">
                                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif
    <!-- Coal Quality Section -->
    <section id="home-coal-products" class="about-section coal-quality-section overflow-hidden" aria-labelledby="coal-quality-heading">
        <div class="about-bg" style="background-image: url('{{ asset('assets/img/bg-img/our-coal-background-v2.png') }}');" aria-hidden="true"></div>

        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="about-content white-content slide-anim" data-scroll-repeat>
                        <div class="section-heading white-content mb-30">
                            <h4 class="sub-heading">{{ __('PRODUCT & QUALITY') }}</h4>
                            <h2 id="coal-quality-heading" class="section-title cursor-effect">{{ __('Our Coal') }}<br> {{ __('Is Characterised') }} <span>{{ __('By Consistent') }} <br> {{ __('High Quality.') }}</span></h2>
                        </div>
                        <p>{{ __('These attributes position our coal as a reliable and competitive fuel source for a wide range of industrial and power generation applications.') }}</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <dl class="coal-quality-grid">
                        <div class="coal-quality-card slide-anim" data-scroll-repeat>
                            <dt class="coal-quality-label">
                                <span class="coal-quality-icon" aria-hidden="true"><i class="fa-regular fa-temperature-high"></i></span>
                                <span>{{ __('High AFT') }}</span>
                            </dt>
                            <dd class="coal-quality-value"><span class="coal-quality-symbol">&gt;</span>1,300<span class="coal-quality-unit">&deg;C</span></dd>

                        </div>
                        <div class="coal-quality-card slide-anim" data-scroll-repeat data-delay="0.08">
                            <dt class="coal-quality-label">
                                <span class="coal-quality-icon" aria-hidden="true"><i class="fa-regular fa-flask"></i></span>
                                <span>{{ __('Low Sulphur') }}</span>
                            </dt>
                            <dd class="coal-quality-value"><span class="coal-quality-symbol">&lt;</span>0,25<span class="coal-quality-unit">%</span></dd>

                        </div>
                        <div class="coal-quality-card slide-anim" data-scroll-repeat data-delay="0.16">
                            <dt class="coal-quality-label">
                                <span class="coal-quality-icon" aria-hidden="true"><i class="fa-regular fa-mountain"></i></span>
                                <span>GAR &ndash; CDE</span>
                            </dt>
                            <dd class="coal-quality-value">5,100</dd>

                        </div>
                        <div class="coal-quality-card slide-anim" data-scroll-repeat data-delay="0.24">
                            <dt class="coal-quality-label">
                                <span class="coal-quality-icon" aria-hidden="true"><i class="fa-regular fa-mountain"></i></span>
                                <span>GAR &ndash; CES</span>
                            </dt>
                            <dd class="coal-quality-value">4,800</dd>

                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </section>

    <!-- CSR & Environment Articles -->
    @include('partials.home-article-section', [
        'sectionId' => 'home-csr',
        'spacing' => 'pt-150 pb-120',
        'category' => $csrCategory,
        'categorySlug' => 'csr-environment',
        'label' => __('Latest Update'),
        'title' => 'CSR',
        'highlight' => __('Environment'),
        'description' => __('Discover our efforts to support local communities and care for the environment. Through our CSR initiatives, we aim to create lasting value for people and future generations.'),
    ])

    <!-- Insights & Trends Articles -->
    @include('partials.home-insights-section')

    {{-- Our Company: Antra Home 7 About layout --}}
    @include('partials.home-company-section')

    {{-- Board of Directors --}}
    @include('partials.home-bod-section')

    {{-- Vision & Mission: adapted from Antra Home 5's process layout --}}
    @include('partials.home-vision-mission-section')


    <!-- Core Values: Antra service layout with the SUCCESS values -->
    @include('partials.home-core-values-section')

</x-layouts.public>
