@push('structured-data')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'ImageGallery',
    'name'     => __('Photo Gallery'),
    'url'      => route('about.photo-gallery'),
    'description' => __('Visual moments, documented activities, and multimedia assets from our company.'),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_TAG) !!}
</script>
@endpush

<x-layouts.public
    :title="__('Photo Gallery')"
    :metaDescription="__('Visual moments, documented activities, and multimedia assets from our company.')"
    :canonicalUrl="route('about.photo-gallery')"
>
    <div class="home-header-background" aria-hidden="true"></div>

    {{-- ===========================
         Section Header
    =========================== --}}
    <section class="pt-130 pb-80" aria-labelledby="photo-gallery-heading">
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
                            $photoGalleryTitle = __('Photo Gallery');
                            $galleryWords = explode(' ', $photoGalleryTitle, 2);
                            $galleryFirstWord = $galleryWords[0] ?? $photoGalleryTitle;
                            $gallerySecondWord = $galleryWords[1] ?? '';
                        @endphp
                        <h1 id="photo-gallery-heading" class="section-title cursor-effect title-2">
                            {{ $galleryFirstWord }}@if($gallerySecondWord) <span>{{ $gallerySecondWord }}</span>@endif
                        </h1>
                    </div>
                </div>
            </div>

            {{-- ===========================
                 Category Filter Tabs
            =========================== --}}
            @if($categories->isNotEmpty())
            <div class="about-gallery-filter mb-50 slide-anim" data-scroll-repeat>
                <a href="{{ route('about.photo-gallery') }}"
                   class="about-gallery-filter-btn {{ !request('category') ? 'active' : '' }}">
                    {{ __('All Photos') }}
                    <span class="filter-count">{{ $galleries->total() }}</span>
                </a>
                @foreach($categories as $cat)
                    @if($cat->galleries_count > 0)
                    <a href="{{ route('about.photo-gallery', ['category' => $cat->slug]) }}"
                       class="about-gallery-filter-btn {{ request('category') === $cat->slug ? 'active' : '' }}">
                        {{ $cat->getTranslation('name', app()->getLocale()) }}
                        <span class="filter-count">{{ $cat->galleries_count }}</span>
                    </a>
                    @endif
                @endforeach
            </div>
            @endif

            {{-- ===========================
                 Gallery Grid — Antra Style 2
                 Layout: 1 tall foto kiri + 2x2 grid kanan
            =========================== --}}
            @if($galleries->getCollection()->isEmpty())
                <div class="about-gallery-empty slide-anim" data-scroll-repeat>
                    <div class="empty-icon">
                        <i class="fa-regular fa-image" aria-hidden="true"></i>
                    </div>
                    <p>{{ __('No photos available yet. Please check back later.') }}</p>
                </div>
            @else
                @php
                    $chunks = $galleries->getCollection()->chunk(5); // 5 foto per baris (1 tall + 4 small di grid 2x2) sesuai template Antra asli
                @endphp

                @foreach($chunks as $chunkIndex => $chunk)
                @php
                    $tall  = $chunk->first();
                    $smalls = $chunk->slice(1);
                    $isEven = $chunkIndex % 2 === 0; // alternasi kiri-kanan
                @endphp

                <div class="row g-4 about-gallery-row mb-24">

                    {{-- Kolom Tall (left / right bergantian, 50% width) --}}
                    @if($tall)
                    <div class="col-lg-6 col-md-6 {{ $isEven ? '' : 'order-lg-2' }}">
                        <div class="gallary-inner-item-2 about-gallery-tall">
                            <a href="{{ $tall->imageUrl() }}"
                               class="venobox img-popup about-gallery-link"
                               data-gall="gallery-group-{{ $chunkIndex }}"
                               data-vbtype="image"
                               title="{{ $tall->getTranslation('title', app()->getLocale()) }}"
                               aria-label="{{ $tall->getTranslation('title', app()->getLocale()) }}">
                                <img src="{{ $tall->imageUrl() }}"
                                     alt="{{ $tall->alt_text ?? $tall->getTranslation('title', app()->getLocale()) }}"
                                     loading="{{ $chunkIndex === 0 ? 'eager' : 'lazy' }}"
                                     fetchpriority="{{ $chunkIndex === 0 ? 'high' : 'auto' }}"
                                     decoding="async"
                                     onload="this.classList.add('is-loaded'); this.closest('.about-gallery-link')?.classList.add('is-loaded');"
                                     onerror="this.classList.add('is-loaded'); this.closest('.about-gallery-link')?.classList.add('is-loaded');">
                                <div class="about-gallery-overlay">
                                    <div class="about-gallery-overlay-inner">
                                        <span class="about-gallery-icon">
                                            <i class="fa-solid fa-magnifying-glass-plus" aria-hidden="true"></i>
                                        </span>
                                        @if($tall->category)
                                        <span class="about-gallery-cat">
                                            {{ $tall->category->getTranslation('name', app()->getLocale()) }}
                                        </span>
                                        @endif
                                        <h3 class="about-gallery-title">
                                            {{ $tall->getTranslation('title', app()->getLocale()) }}
                                        </h3>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    @endif

                    {{-- Kolom 2x2 grid (4 foto, 50% width) --}}
                    @if($smalls->isNotEmpty())
                    <div class="col-lg-6 col-md-6 {{ $isEven ? '' : 'order-lg-1' }}">
                        <div class="gallary-inner-items about-gallery-grid @if($smalls->count() < 4) count-{{ $smalls->count() }} @endif">
                            @foreach($smalls as $small)
                            <div class="gallary-inner-item-2 about-gallery-small">
                                <a href="{{ $small->imageUrl() }}"
                                   class="venobox img-popup about-gallery-link"
                                   data-gall="gallery-group-{{ $chunkIndex }}"
                                   data-vbtype="image"
                                   title="{{ $small->getTranslation('title', app()->getLocale()) }}"
                                   aria-label="{{ $small->getTranslation('title', app()->getLocale()) }}">
                                    <img src="{{ $small->imageUrl() }}"
                                         alt="{{ $small->alt_text ?? $small->getTranslation('title', app()->getLocale()) }}"
                                         loading="{{ $chunkIndex === 0 ? 'eager' : 'lazy' }}"
                                         fetchpriority="{{ $chunkIndex === 0 ? 'high' : 'auto' }}"
                                         decoding="async"
                                         onload="this.classList.add('is-loaded'); this.closest('.about-gallery-link')?.classList.add('is-loaded');"
                                         onerror="this.classList.add('is-loaded'); this.closest('.about-gallery-link')?.classList.add('is-loaded');">
                                    <div class="about-gallery-overlay">
                                        <div class="about-gallery-overlay-inner">
                                            <span class="about-gallery-icon">
                                                <i class="fa-solid fa-magnifying-glass-plus" aria-hidden="true"></i>
                                            </span>
                                            @if($small->category)
                                            <span class="about-gallery-cat">
                                                {{ $small->category->getTranslation('name', app()->getLocale()) }}
                                            </span>
                                            @endif
                                            <h3 class="about-gallery-title">
                                                {{ $small->getTranslation('title', app()->getLocale()) }}
                                            </h3>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>{{-- /.row --}}
                @endforeach

                @if($galleries->hasPages())
                <div class="about-gallery-pagination mt-60">
                    {{ $galleries->links('pagination::bootstrap-4') }}
                </div>
                @endif

            @endif

        </div>{{-- /.container --}}
    </section>

</x-layouts.public>

@push('structured-data')
@php
    $galleryItems = $galleries->getCollection()->map(function($g) {
        return [
            '@type'       => 'ImageObject',
            'contentUrl'  => $g->imageUrl(),
            'name'        => $g->getTranslation('title', app()->getLocale()),
            'description' => $g->getTranslation('description', app()->getLocale()) ?: '',
        ];
    })->values()->all();

    $gallerySchema = [
        '@context' => 'https://schema.org',
        '@type'    => 'ImageGallery',
        'name'     => __('Photo Gallery'),
        'url'      => request()->fullUrl(),
        'image'    => $galleryItems,
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($gallerySchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@push('scripts')
<script>
    (function () {
        function checkGalleryLoaded() {
            document.querySelectorAll('.about-gallery-link img').forEach(function (img) {
                if (img.complete && img.naturalWidth > 0) {
                    img.classList.add('is-loaded');
                    img.closest('.about-gallery-link')?.classList.add('is-loaded');
                }
            });
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', checkGalleryLoaded);
        } else {
            checkGalleryLoaded();
        }
    })();
</script>
@endpush
