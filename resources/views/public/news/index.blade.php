@php
    $listingUrl = $activeCategory ? route('news.category', ['category' => $activeCategory->slug]) : route('news.index');
    $pageHeading = $activeCategory?->getTranslation('name', app()->getLocale()) ?? __('News');
    $listTitle = $activeCategory ? $pageHeading . ' — ' . __('News') : __('News');
    $listDesc = $activeCategory
        ? __('Browse articles in the :category category.', ['category' => $pageHeading])
        : __('Browse published articles, insights and publications across all categories.');
    $shouldNoIndex = request()->hasAny(['search', 'tag']) || $articles->currentPage() > 1;
@endphp

@push('structured-data')
@php
    $listItems = $articles->map(function($art, $i) {
        $slug = $art->getTranslation('slug', app()->getLocale()) ?: $art->id;
        return [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'url'      => $art->publicUrl(),
            'name'     => $art->getTranslation('title', app()->getLocale()),
        ];
    })->values()->all();

    $itemListSchema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'ItemList',
        'name'            => $listTitle,
        'url'             => request()->fullUrl(),
        'itemListElement' => $listItems,
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($itemListSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_TAG) !!}
</script>
@endpush

<x-layouts.public :title="$listTitle" :metaDescription="$listDesc" :noIndex="$shouldNoIndex" :canonicalUrl="$listingUrl">
    <div class="home-header-background" aria-hidden="true"></div>
    <section class="news-hero-section" aria-labelledby="news-heading">
        <div class="container container-2">
            <nav class="news-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
                <a href="{{ route('home') }}">{{ __('Home') }}</a>
                <span aria-hidden="true">/</span>
                @if($activeCategory)
                    <a href="{{ route('news.index') }}">{{ __('News') }}</a>
                    <span aria-hidden="true">/</span>
                    <span aria-current="page" style="color: #164e39; font-weight: 600;">{{ $pageHeading }}</span>
                @else
                    <span aria-current="page" style="color: #164e39; font-weight: 600;">{{ __('News') }}</span>
                @endif
            </nav>

            <div class="news-hero-content">
                <div class="news-hero-badge">
                    <span class="badge-dot"></span>
                    <span>{{ $activeCategory ? __('Article Category') : __('Company News & Insights') }}</span>
                </div>
                <h1 id="news-heading" class="section-title title-2 cursor-effect">
                    @if($activeCategory)
                        @php
                            $words = explode(' ', trim($pageHeading));
                        @endphp
                        @if(count($words) > 1)
                            {{ Str::beforeLast($pageHeading, ' ') }} <span>{{ Str::afterLast($pageHeading, ' ') }}</span>
                        @else
                            <span>{{ $pageHeading }}</span>
                        @endif
                    @else
                        {{ __('Latest News &') }} <span>{{ __('Insights') }}</span>
                    @endif
                </h1>
                <p class="news-hero-lead">{{ $listDesc }}</p>
                @if($activeTag)
                    <p class="news-active-tag">{{ __('Articles tagged') }} <strong>#{{ $activeTag->name }}</strong></p>
                @endif
            </div>
        </div>
    </section>

    <section class="blog-section news-list-section" aria-label="{{ __('Articles') }}">
        <div class="container container-2">
            <div class="news-toolbar news-category-toolbar">
                <form action="{{ $listingUrl }}" method="GET" class="news-search" role="search">
                    @if(request('tag'))
                        <input type="hidden" name="tag" value="{{ request('tag') }}">
                    @endif
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="{{ __('Search articles...') }}" aria-label="{{ __('Search articles...') }}">
                    <button type="submit" aria-label="{{ __('Search') }}">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg>
                    </button>
                </form>
            </div>
            @if($tags->isNotEmpty())
                <div class="news-tags">
                    <span>{{ __('Tags:') }}</span>
                    @foreach($tags as $tag)
                        <a href="{{ $listingUrl . '?' . http_build_query(array_filter(array_merge(request()->only(['search']), ['tag' => request('tag') === $tag->slug ? null : $tag->slug]))) }}"
                           class="news-tag" @if(request('tag') === $tag->slug) aria-current="true" @endif>#{{ $tag->name }}</a>
                    @endforeach
                </div>
            @endif
            @if($articles->isNotEmpty())
                <div class="news-grid">
                    @foreach($articles as $article)
                        @include('partials.news-card', ['article' => $article])
                    @endforeach
                </div>
            @else
                <div class="news-empty">
                    <p>{{ __('No articles found matching your criteria.') }}</p>
                    @if(request()->hasAny(['search', 'tag']))
                        <a href="{{ $listingUrl }}">{{ __('Clear all filters') }}</a>
                    @endif
                </div>
            @endif
            @if($articles->hasPages())
                <div class="news-pagination">{{ $articles->onEachSide(1)->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </section>
</x-layouts.public>
