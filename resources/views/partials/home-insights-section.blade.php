@php
    $insightsArticles = $insightsCategory?->articles ?? collect();
@endphp

<section id="home-insights" class="gallary-section-2 home-insights-section pt-130 pb-130 overflow-hidden" aria-labelledby="home-insights-heading">
    <div class="bg-img home-insights-background" aria-hidden="true"></div>
    <div class="container container-2">
        <div class="row align-items-center gy-5">
            <div class="col-lg-4 col-md-12">
                <div class="gallary-left-content home-insights-intro slide-anim" data-scroll-repeat data-direction="left">
                    <div class="section-heading white-content mb-0">
                        <h4 class="sub-heading">{{ __('Latest Articles') }}</h4>
                        <h2 id="home-insights-heading" class="section-title cursor-effect title-2">{{ __('Insights &') }}<br> <span>{{ __('Trends') }}</span></h2>
                        <p>{{ __('Explore perspectives on the coal industry, energy, and developments shaping the sector. Find insights and trends to help you understand the opportunities and challenges ahead.') }}</p>
                    </div>
                    <a href="{{ route('news.category', ['category' => $insightsCategory?->slug ?? 'insights-trends']) }}" class="tl-primary-btn white-btn home-insights-all">{{ __('View All') }} <span class="icon"><i class="fa-regular fa-arrow-right" aria-hidden="true"></i></span></a>
                </div>
            </div>
            <div class="col-lg-8">
                @if ($insightsArticles->isNotEmpty())
                    <div class="gallary-carousel-wrap home-insights-rail slide-anim" data-scroll-repeat data-direction="right" data-delay="0.12">
                        <div class="home-insights-slider swiper" aria-label="{{ $insightsCategory->getTranslation('name', app()->getLocale()) }}">
                            <div class="swiper-wrapper">
                                @foreach ($insightsArticles as $article)
                                    <article class="swiper-slide home-insights-item" data-insights-article-id="{{ $article->id }}">
                                        <a class="home-insights-card" href="{{ $article->publicUrl() }}" aria-labelledby="home-insights-title-{{ $article->id }}">
                                            <div class="home-insights-card-media">
                                                @if ($article->thumbnailUrl())
                                                    <img class="home-insights-thumbnail" src="{{ $article->thumbnailUrl() }}" alt="" loading="lazy" decoding="async">
                                                @else
                                                    <span class="home-insights-placeholder" aria-hidden="true"><i class="fa-regular fa-mountain"></i></span>
                                                @endif
                                            </div>
                                            <div class="home-insights-card-content">
                                                <h3 id="home-insights-title-{{ $article->id }}" class="home-insights-title">{{ $article->title }}</h3>
                                                <div class="home-insights-card-footer">
                                                    <time datetime="{{ ($article->published_at ?? $article->created_at)->toDateString() }}">{{ ($article->published_at ?? $article->created_at)->locale(app()->getLocale())->translatedFormat('d M Y') }}</time>
                                                    <span class="home-insights-read">{{ __('Read Article') }} <i class="fa-regular fa-arrow-up-right" aria-hidden="true"></i></span>
                                                </div>
                                            </div>
                                        </a>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                        @if ($insightsArticles->count() > 1)
                            <div class="swiper-arrow home-insights-navigation">
                                <button class="swiper-nav home-insights-prev" type="button" aria-label="{{ __('Previous article') }}"><i class="fa-regular fa-arrow-left" aria-hidden="true"></i></button>
                                <button class="swiper-nav home-insights-next" type="button" aria-label="{{ __('Next article') }}"><i class="fa-regular fa-arrow-right" aria-hidden="true"></i></button>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="home-insights-empty">
                        <i class="fa-regular fa-newspaper" aria-hidden="true"></i>
                        <p>{{ __('No articles to display in this category yet.') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
