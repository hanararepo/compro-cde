<section id="{{ $sectionId }}" class="blog-section {{ $spacing }} tl-bg-color" aria-labelledby="{{ $sectionId }}-heading">
    <div class="container container-2">
        <div class="row section-heading-wrap slide-anim" data-scroll-repeat>
            <div class="shape"><img src="{{ asset('assets/img/shapes/section-heading.png') }}" alt="" aria-hidden="true"></div>
            <div class="col-lg-4 col-md-12">
                <div class="section-heading mb-0">
                    <h4 class="sub-heading">{{ $label }}</h4>
                </div>
            </div>
            <div class="col-lg-8 col-md-12 home-updates-heading d-flex justify-content-between align-items-end flex-wrap gap-4">
                <div class="section-heading section-heading-2 mb-0">
                    <h2 id="{{ $sectionId }}-heading" class="section-title cursor-effect title-2">{{ $title }} <span>{{ $highlight }}</span></h2>
                    <p class="home-updates-description">{{ $description }}</p>
                </div>
                <div class="flex-shrink-0">
                    <a href="{{ route('news.category', ['category' => $category?->slug ?? $categorySlug]) }}" class="tl-primary-btn mt-2">{{ __('View All') }} <span class="icon"><i class="fa-regular fa-arrow-right" aria-hidden="true"></i></span></a>
                </div>
            </div>
        </div>

        <div class="row mt-40">
            @forelse($category?->articles ?? collect() as $article)
                <div class="col-lg-4 col-md-6 mb-40">
                    <article class="post-card slide-anim" data-scroll-repeat data-direction="{{ ['left', 'bottom', 'right'][$loop->index % 3] }}" data-delay="{{ $loop->index * 0.08 }}">
                        <div class="post-thumb">
                            <a href="{{ $article->publicUrl() }}">
                                <img src="{{ $article->thumbnailUrl() ?: asset('assets/img/blog/post-1.jpg') }}"
                                     alt="{{ $article->title }}" loading="lazy" decoding="async">
                            </a>
                        </div>
                        <div class="post-content">
                            <ul class="post-meta">
                                <li>{{ $article->published_at ? $article->published_at->format('M d, Y') : $article->created_at->format('M d, Y') }}</li>
                            </ul>
                            <h3 class="title">
                                <a href="{{ $article->publicUrl() }}">{{ $article->title }}</a>
                            </h3>
                            <p>{{ Str::limit(strip_tags($article->content), 100) }}</p>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <p class="text-muted">{{ __('No articles to display in this category yet.') }}</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
