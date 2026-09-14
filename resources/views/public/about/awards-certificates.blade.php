@push('structured-data')
@php
    $awardsSchema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'ItemList',
        'name'            => __('Awards & Certifications'),
        'url'             => route('about.awards-certificates'),
        'description'     => __('Recognitions, awards, and industry certifications achieved by our company in demonstrating excellence and quality.'),
        'itemListElement' => $awards->merge($certificates)->values()->map(function ($item, $i) {
            return [
                '@type'    => 'ListItem',
                'position' => $i + 1,
                'name'     => $item->title,
            ];
        })->all(),
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($awardsSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_TAG) !!}
</script>
@endpush

<x-layouts.public
    :title="__('Awards & Certification')"
    :metaDescription="__('Recognitions, awards, and industry certifications achieved by our company in demonstrating excellence and quality.')"
    :canonicalUrl="route('about.awards-certificates')"
>
    <div class="home-header-background" aria-hidden="true"></div>

    {{-- Scoped & Fallback Styling for Awards & Certificates --}}
    <style>
        .section-heading-wrap {
            position: relative;
            padding-top: 60px;
            margin-bottom: 45px;
        }
        .section-heading-wrap .shape {
            position: absolute;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: 0;
        }
        .section-heading-wrap .section-title {
            font-size: clamp(28px, 3.8vw, 48px);
            line-height: 1.18;
            position: relative;
            z-index: 1;
        }

        /* Award Card (Compact Horizontal Split for Tall Portrait Poster / Plaque) */
        .award-card-item {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            margin: 0 auto 28px;
            max-width: 960px;
        }
        .award-card-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.08);
            border-color: rgba(48, 170, 71, 0.45);
        }
        .award-card-image {
            position: relative;
            width: 100%;
            height: 100%;
            min-height: 320px;
            max-height: 390px;
            background: linear-gradient(145deg, #f8fafc 0%, #edf2f7 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 16px;
        }
        @media (max-width: 767px) {
            .award-card-image {
                min-height: 280px;
                max-height: 350px;
                border-bottom: 1px solid #e2e8f0;
            }
        }
        .award-card-image a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }
        .award-card-image img {
            max-width: 100%;
            max-height: 350px;
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            transition: transform 0.35s ease;
        }
        .award-card-item:hover .award-card-image img {
            transform: scale(1.02);
        }
        .award-card-zoom {
            position: absolute;
            bottom: 14px;
            right: 14px;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #ffffff;
            color: #30aa47;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            opacity: 0;
            transform: scale(0.85);
            transition: all 0.25s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 2;
        }
        .award-card-item:hover .award-card-zoom {
            opacity: 1;
            transform: scale(1);
        }
        .award-card-body {
            padding: 24px 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
        }
        @media (max-width: 767px) {
            .award-card-body {
                padding: 20px 16px;
            }
        }
        .award-card-date {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12.5px;
            font-weight: 600;
            color: #30aa47;
            background: rgba(48, 170, 71, 0.08);
            border: 1px solid rgba(48, 170, 71, 0.2);
            padding: 3px 10px;
            border-radius: 100px;
            width: fit-content;
            margin-bottom: 12px;
        }
        .award-card-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--tl-color-heading-primary, #0f172a);
            margin-bottom: 12px;
            line-height: 1.4;
        }
        .award-card-desc {
            font-size: 14px;
            color: #475569;
            line-height: 1.68;
        }
        .award-card-desc p {
            margin-bottom: 10px;
        }
        .award-card-desc p:last-child {
            margin-bottom: 0;
        }

        /* Certificate Gallery Item */
        .cert-gallery-item {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            display: block;
            aspect-ratio: 3 / 4;
        }
        .cert-gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 32px rgba(0, 0, 0, 0.12);
            border-color: rgba(48, 170, 71, 0.5);
        }
        .cert-gallery-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            transition: transform 0.5s ease;
        }
        .cert-gallery-item:hover .cert-gallery-img {
            transform: scale(1.06);
        }
        .cert-gallery-overlay {
            position: absolute;
            inset: 0;
            border-radius: 16px;
            background: linear-gradient(
                to top,
                rgba(13, 40, 24, 0.94) 0%,
                rgba(13, 40, 24, 0.5) 55%,
                rgba(13, 40, 24, 0.1) 100%
            );
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 20px;
            opacity: 0;
            transition: opacity 0.35s ease;
            z-index: 3;
        }
        .cert-gallery-item:hover .cert-gallery-overlay {
            opacity: 1;
        }
        .cert-gallery-content {
            transform: translateY(12px);
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .cert-gallery-item:hover .cert-gallery-content {
            transform: translateY(0);
        }
        .cert-gallery-title {
            color: #ffffff;
            font-size: 16px;
            font-weight: 700;
            line-height: 1.35;
            margin-bottom: 6px;
        }
        .cert-gallery-icon {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: rgba(255, 255, 255, 0.85);
            font-size: 12px;
            font-weight: 600;
        }
        .cert-gallery-icon i {
            font-size: 13px;
            color: #86efac;
        }
    </style>

    {{-- ===========================
         Section Header
    =========================== --}}
    <section class="pt-130 pb-80" aria-labelledby="awards-heading">
        <div class="container container-2">

            {{-- Section Heading --}}
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
                            $locale = app()->getLocale();
                            $headingFirst = $locale === 'id' ? 'Penghargaan &' : 'Awards &';
                            $headingSecond = $locale === 'id' ? 'Sertifikat' : 'Certificate';
                        @endphp
                        <h1 id="awards-heading" class="section-title cursor-effect title-2">
                            {{ $headingFirst }} <span>{{ $headingSecond }}</span>
                        </h1>
                    </div>
                </div>
            </div>

            {{-- Alpine Container for Tab State (Awards active by default) --}}
            <div x-data="{ activeTab: 'awards' }">

                {{-- Clean Filter Tabs: Awards & Certificates --}}
                <div class="about-gallery-filter mb-45 slide-anim" data-scroll-repeat>
                    <button type="button"
                            @click="activeTab = 'awards'"
                            class="about-gallery-filter-btn"
                            :class="{ 'active': activeTab === 'awards' }">
                        <span>{{ __('Awards') }}</span>
                        <span class="filter-count">{{ $awards->count() }}</span>
                    </button>
                    <button type="button"
                            @click="activeTab = 'certificates'"
                            class="about-gallery-filter-btn"
                            :class="{ 'active': activeTab === 'certificates' }">
                        <span>{{ __('Certificate') }}</span>
                        <span class="filter-count">{{ $certificates->count() }}</span>
                    </button>
                </div>

                {{-- Empty State when everything is empty --}}
                @if($awards->isEmpty() && $certificates->isEmpty())
                    <div class="about-gallery-empty slide-anim" data-scroll-repeat>
                        <div class="empty-icon">
                            <i class="fa-solid fa-award" aria-hidden="true"></i>
                        </div>
                        <p>{{ __('No awards or certificates available at this time. Please check back later.') }}</p>
                    </div>
                @endif

                {{-- ========================================================
                     AWARDS SECTION (Compact Side-by-Side)
                     ======================================================== --}}
                <div x-show="activeTab === 'awards'"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     class="mb-60">

                    @if($awards->isNotEmpty())
                        @foreach($awards as $award)
                            @php
                                $awardTitle = $award->getTranslation('title', app()->getLocale());
                                $awardDesc = $award->getTranslation('description', app()->getLocale());
                            @endphp
                            <article class="award-card-item">
                                <div class="row g-0 align-items-stretch">
                                    {{-- Left: Compact Portrait Image Container --}}
                                    <div class="col-lg-4 col-md-5">
                                        <div class="award-card-image">
                                            <a href="{{ $award->imageUrl() }}"
                                               class="venobox img-popup"
                                               data-gall="awards-gallery"
                                               data-vbtype="image"
                                               title="{{ $awardTitle }}"
                                               aria-label="{{ $awardTitle }}">
                                                <img src="{{ $award->imageUrl() }}"
                                                     alt="{{ $awardTitle }}"
                                                     loading="lazy"
                                                     decoding="async">
                                                <span class="award-card-zoom" aria-hidden="true">
                                                    <i class="fa-solid fa-magnifying-glass-plus"></i>
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                    {{-- Right: Date, Title & Article Description --}}
                                    <div class="col-lg-8 col-md-7 d-flex flex-column justify-content-center">
                                        <div class="award-card-body">
                                            @if($award->issued_date)
                                                <div class="award-card-date">
                                                    <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                                                    <span>{{ $award->issued_date->translatedFormat('d F Y') }}</span>
                                                </div>
                                            @endif
                                            <h3 class="award-card-title">{{ $awardTitle }}</h3>
                                            @if($awardDesc)
                                                <div class="award-card-desc article-content">
                                                    {!! $awardDesc !!}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    @elseif($awards->isEmpty() && $certificates->isNotEmpty())
                        <div x-show="activeTab === 'awards'" class="about-gallery-empty">
                            <div class="empty-icon">
                                <i class="fa-solid fa-trophy" aria-hidden="true"></i>
                            </div>
                            <p>{{ __('No awards available yet.') }}</p>
                        </div>
                    @endif
                </div>

                {{-- ========================================================
                     CERTIFICATIONS SECTION (Gallery: Clean Image, Hover Title)
                     ======================================================== --}}
                <div x-show="activeTab === 'certificates'"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0">

                    @if($certificates->isNotEmpty())
                        <div class="row g-4">
                            @foreach($certificates as $cert)
                                @php
                                    $certTitle = $cert->getTranslation('title', app()->getLocale());
                                @endphp
                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <a href="{{ $cert->imageUrl() }}"
                                       class="cert-gallery-item venobox img-popup"
                                       data-gall="cert-gallery"
                                       data-vbtype="image"
                                       title="{{ $certTitle }}"
                                       aria-label="{{ $certTitle }}">
                                        {{-- Clean plain certificate image by default --}}
                                        <img src="{{ $cert->imageUrl() }}"
                                             alt="{{ $certTitle }}"
                                             class="cert-gallery-img"
                                             loading="lazy"
                                             decoding="async">

                                        {{-- Hover Overlay revealing Title & Zoom Icon --}}
                                        <div class="cert-gallery-overlay">
                                            <div class="cert-gallery-content">
                                                <h4 class="cert-gallery-title">{{ $certTitle }}</h4>
                                                @if($cert->issued_date)
                                                    <span style="font-size: 12px; color: rgba(255,255,255,0.8); display: block; margin-bottom: 6px;">
                                                        <i class="fa-regular fa-calendar me-1"></i>{{ $cert->issued_date->translatedFormat('d F Y') }}
                                                    </span>
                                                @endif
                                                <span class="cert-gallery-icon">
                                                    <i class="fa-solid fa-magnifying-glass-plus" aria-hidden="true"></i>
                                                    {{ __('View Certificate') }}
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @elseif($certificates->isEmpty() && $awards->isNotEmpty())
                        <div x-show="activeTab === 'certificates'" class="about-gallery-empty">
                            <div class="empty-icon">
                                <i class="fa-solid fa-certificate" aria-hidden="true"></i>
                            </div>
                            <p>{{ __('No certificates available yet.') }}</p>
                        </div>
                    @endif
                </div>

            </div>

        </div>
    </section>
</x-layouts.public>
