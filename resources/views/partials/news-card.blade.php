@php
    $cardTitle = $article->getTranslation('title', app()->getLocale());
    $cardSummary = trim(strip_tags($article->getTranslation('summary', app()->getLocale())));
    $cardUrl = $article->publicUrl();
    $cardDate = $article->published_at ?? $article->created_at;
@endphp
<article class="post-card news-card">
    <div class="post-thumb">
        <a class="news-card-image" href="{{ $cardUrl }}" aria-label="{{ $cardTitle }}">
            @if($article->thumbnail)
                <img src="{{ $article->thumbnailUrl() }}" alt="{{ $cardTitle }}" loading="lazy" decoding="async">
            @else
                <span class="news-image-placeholder" aria-hidden="true">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="3" width="18" height="18" rx="3"/>
                        <path d="m3 16 5-5 4 4 3-3 6 6"/><circle cx="15.5" cy="8.5" r="1.5"/>
                    </svg>
                </span>
            @endif
        </a>

    </div>
    <div class="post-content">
        <time class="news-card-date" datetime="{{ $cardDate->toDateString() }}">{{ $cardDate->translatedFormat('d M Y') }}</time>
        <h2 class="title"><a href="{{ $cardUrl }}">{{ $cardTitle }}</a></h2>
        @if($cardSummary !== '')
            <p class="news-card-summary">{{ $cardSummary }}</p>
        @endif
        <a class="news-read-link" href="{{ $cardUrl }}">{{ __('Read Full Article') }} <span aria-hidden="true">&nearr;</span></a>
    </div>
</article>
