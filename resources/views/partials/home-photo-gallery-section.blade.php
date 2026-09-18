@if($galleries->isNotEmpty())
    <section id="home-photo-gallery" class="gallary-section home-photo-gallery" aria-labelledby="home-photo-gallery-heading">
        <h2 id="home-photo-gallery-heading" class="visually-hidden">{{ __('Photo Gallery') }}</h2>
        <div class="gallary-text" aria-hidden="true"><span>{{ __('Gallery') }}</span></div>

        @foreach($galleries->chunk(4) as $rowIndex => $photos)
            <div class="gallary-wrap home-photo-gallery-row {{ $rowIndex === 0 ? 'wrap-1' : 'gallery-scroll-direction-ltr' }}">
                <div class="gallery-scroll-wrap {{ $photos->count() === 4 ? 'home-photo-gallery-full-row' : '' }}" style="--gallery-columns: {{ $photos->count() }}">
                    @foreach($photos as $photo)
                        @php($photoTitle = $photo->getTranslation('title', app()->getLocale()) ?: __('Photo Gallery'))
                        <div class="gallary-scroll-item" data-gallery-photo-id="{{ $photo->id }}">
                            <a href="{{ $photo->imageUrl() }}" class="venobox home-photo-gallery-link"
                               data-gall="home-photo-gallery" data-vbtype="image"
                               title="{{ $photoTitle }}" aria-label="{{ $photoTitle }}">
                                <img src="{{ $photo->thumbnailUrl() }}" alt="{{ $photo->alt_text ?: $photoTitle }}"
                                     width="680" height="440" loading="lazy" decoding="async">
                                <span class="home-photo-gallery-caption" aria-hidden="true">
                                    <span>{{ $photoTitle }}</span>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 3H3v5m13-5h5v5M3 16v5h5m13-5v5h-5M8 12h8m-4-4v8"/></svg>
                                </span>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </section>
@endif
