@push('structured-data')
@php
    $articleTitle      = $article->getTranslation('title', app()->getLocale());
    $articleSummary    = $article->getTranslation('summary', app()->getLocale());
    $articleSlug       = $article->getTranslation('slug', app()->getLocale()) ?: $article->id;
    $articleUrl        = $article->publicUrl();
    $listingUrl        = $article->category ? route('news.category', ['category' => $article->category->slug]) : route('news.index');
    $articleImage      = $article->thumbnail ? asset('storage/' . $article->thumbnail) : '';
    $articlePublished  = ($article->published_at ?? $article->created_at)->toIso8601String();
    $articleModified   = $article->updated_at->toIso8601String();
    $articleAuthorName = $article->author->name;
    $articlePublisher  = \App\Models\Setting::get('site_name', config('app.name'));

    // Build Article schema
    $articleSchema = [
        '@context'      => 'https://schema.org',
        '@type'         => 'Article',
        'headline'      => $articleTitle,
        'description'   => $articleSummary,
        'datePublished' => $articlePublished,
        'dateModified'  => $articleModified,
        'author'        => ['@type' => 'Person', 'name' => $articleAuthorName],
        'publisher'     => ['@type' => 'Organization', 'name' => $articlePublisher],
        'url'           => $articleUrl,
    ];
    if ($articleImage) { $articleSchema['image'] = $articleImage; }

    // Build BreadcrumbList schema
    $breadcrumbItems = [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home',     'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'News', 'item' => route('news.index')],
    ];
    if ($article->category) {
        $breadcrumbItems[] = [
            '@type'    => 'ListItem',
            'position' => 3,
            'name'     => $article->category->getTranslation('name', app()->getLocale()),
            'item'     => $listingUrl,
        ];
        $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => 4, 'name' => $articleTitle, 'item' => $articleUrl];
    } else {
        $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => 3, 'name' => $articleTitle, 'item' => $articleUrl];
    }

    $breadcrumbSchema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $breadcrumbItems,
    ];
@endphp
<script type="application/ld+json">
{!! json_encode([$articleSchema, $breadcrumbSchema], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_TAG) !!}
</script>
@endpush

<x-layouts.public
    :title="$articleTitle"
    :metaDescription="$articleSummary"
    :metaKeywords="$article->tags->pluck('name')->implode(', ')"
    :canonicalUrl="$articleUrl"
    ogType="article"
    :ogImage="$article->thumbnailUrl()"
    :articlePublishedTime="$articlePublished"
    :articleModifiedTime="$articleModified"
    :articleAuthor="$articleAuthorName"
    :articleSection="$article->category?->name"
>
    <div class="home-header-background" aria-hidden="true"></div>
    <article class="blog-details-section news-detail-section">
        <div class="container news-detail-container">
            <nav class="news-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
                <a href="{{ route('home') }}">{{ __('Home') }}</a>
                <span aria-hidden="true">/</span>
                <span>{{ __('News') }}</span>
                @if($article->category)
                    <span aria-hidden="true">/</span>
                    <a href="{{ $listingUrl }}">{{ $article->category->name }}</a>
                @endif
            </nav>
            <header class="news-detail-header">
                @if($article->category)
                    <a class="news-filter" href="{{ $listingUrl }}">{{ $article->category->name }}</a>
                @endif
                <h1>{{ $articleTitle }}</h1>
                <div class="news-author">
                    @if($article->author->avatar)
                        <img class="news-author-avatar" src="{{ $article->author->avatarUrl() }}" alt="" width="48" height="48">
                    @else
                        <span class="news-author-avatar" aria-hidden="true">{{ Str::upper(Str::substr($articleAuthorName, 0, 1)) }}</span>
                    @endif
                    <div>
                        <p>{{ $articleAuthorName }}</p>
                        <p class="news-published">
                            {{ __('Published on') }} <time datetime="{{ $articlePublished }}">{{ ($article->published_at ?? $article->created_at)->translatedFormat('d F Y') }}</time>
                            &middot; {{ number_format($article->views_count) }} {{ __('views') }}
                        </p>
                    </div>
                </div>
            </header>
            @if($article->thumbnail)
                <figure class="news-featured-image">
                    <img src="{{ $article->thumbnailUrl() }}" alt="{{ $articleTitle }}" decoding="async">
                </figure>
            @endif
            {{-- Rich text from the dashboard editor. --}}
            <div class="article-content news-article-content">
                {!! $article->getTranslation('content', app()->getLocale()) !!}
            </div>
            @if($article->tags->isNotEmpty())
                <div class="news-tags news-detail-tags">
                    <span>{{ __('Tags:') }}</span>
                    @foreach($article->tags as $tag)
                        <a href="{{ $listingUrl . '?' . http_build_query(['tag' => $tag->slug]) }}" class="news-tag">#{{ $tag->name }}</a>
                    @endforeach
                </div>
            @endif
        </div>
    </article>
    @if($relatedArticles->isNotEmpty())
        <section class="blog-section news-related-section" aria-labelledby="related-news-heading">
            <div class="container container-2">
                <div class="section-heading">
                    <h2 id="related-news-heading" class="section-title title-2">{{ __('Related Articles') }}</h2>
                </div>
                <div class="news-grid">
                    @foreach($relatedArticles as $related)
                        @include('partials.news-card', ['article' => $related])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.public>
