@push('structured-data')
@php
    $careersListSchema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'ItemList',
        'name'            => __('Careers & Opportunities'),
        'url'             => route('careers.index'),
        'description'     => __('Explore open job positions and career opportunities at our company. Apply today and grow your career with us.'),
        'itemListElement' => $jobs->values()->map(function ($job, $i) {
            return [
                '@type'    => 'ListItem',
                'position' => $i + 1,
                'name'     => $job->getTranslation('title', app()->getLocale()),
                'url'      => route('careers.show', $job->getSlug()),
            ];
        })->all(),
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($careersListSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_TAG) !!}
</script>
@endpush

<x-layouts.public
    :title="__('Careers & Opportunities')"
    :metaDescription="__('Explore open job positions and career opportunities at our company. Apply today and grow your career with us.')"
    :canonicalUrl="route('careers.index')"
>
    <div class="home-header-background" aria-hidden="true"></div>

    {{-- Hero Section --}}
    <section class="careers-hero-section" aria-labelledby="careers-heading">
        <div class="container container-2">
            <nav class="news-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
                <a href="{{ route('home') }}">{{ __('Home') }}</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page">{{ __('Careers') }}</span>
            </nav>

            <div class="careers-hero-badge">
                <span class="badge-dot"></span>
                <span>{{ __('Join Our Team') }}</span>
            </div>

            @php
                $careersHeading = __('Build Your Career With Us');
                $words = explode(' ', $careersHeading);
                $headingLead = count($words) > 2 ? implode(' ', array_slice($words, 0, count($words) - 2)) : ($words[0] ?? $careersHeading);
                $headingHighlight = count($words) > 2 ? implode(' ', array_slice($words, -2)) : ($words[1] ?? '');
            @endphp
            <h1 id="careers-heading">
                {{ $headingLead }} @if($headingHighlight)<span>{{ $headingHighlight }}</span>@endif
            </h1>

            <p class="careers-hero-lead">
                {{ __('Discover open positions, a collaborative culture, and opportunities to make a meaningful impact. Find the right role for you.') }}
            </p>
        </div>
    </section>

    {{-- Job Listings Section --}}
    <section class="careers-list-section" aria-labelledby="open-positions-heading">
        <div class="container container-2">

            <div class="careers-section-header">
                <div>
                    <h2 id="open-positions-heading">{{ __('Open Positions') }}</h2>
                    <p style="margin:0; font-size:14px; color:#6a8070;">
                        {{ __(':count position(s) currently open', ['count' => $jobs->total()]) }}
                    </p>
                </div>
                <span class="careers-count-badge">
                    <i class="fa-solid fa-briefcase" aria-hidden="true"></i>
                    {{ $jobs->total() }} {{ __('Open') }}
                </span>
            </div>

            @if($jobs->isEmpty())
                <div class="careers-empty">
                    <div class="careers-empty-icon">
                        <i class="fa-solid fa-briefcase" aria-hidden="true"></i>
                    </div>
                    <h3>{{ __('No open positions at this time') }}</h3>
                    <p>{{ __('We currently do not have any open roles, but we are always looking for talented individuals. Please check back later or reach out directly.') }}</p>
                    <a href="{{ route('contact') }}" class="careers-empty-cta">
                        <span>{{ __('Contact Us') }}</span>
                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>
            @else
                <div class="careers-grid">
                    @foreach($jobs as $job)
                        @php
                            $title    = $job->getTranslation('title', app()->getLocale(), false) ?: $job->getTranslation('title', 'en', false);
                            $desc     = $job->getTranslation('description', app()->getLocale(), false) ?: $job->getTranslation('description', 'en', false);
                            $cleanDesc = Str::limit(strip_tags($desc), 130);
                        @endphp

                        <article class="career-card">
                            {{-- Image or strip --}}
                            @if($job->image)
                                <div class="career-card-img-wrap">
                                    <img src="{{ $job->imageUrl() }}" alt="{{ $title }}" loading="lazy">
                                    <span class="career-type-badge">
                                        {{ $job->type->label() }}
                                    </span>
                                </div>
                            @else
                                <div class="career-card-strip">
                                    <div class="career-card-icon">
                                        <i class="fa-solid fa-briefcase" aria-hidden="true"></i>
                                    </div>
                                    <span class="career-type-badge">
                                        {{ $job->type->label() }}
                                    </span>
                                </div>
                            @endif

                            {{-- Body --}}
                            <div class="career-card-body">
                                <h3 class="career-card-title">
                                    <a href="{{ route('careers.show', $job->getSlug()) }}" style="color:inherit; text-decoration:none;">
                                        {{ $title }}
                                    </a>
                                </h3>
                                <p class="career-card-desc">{{ $cleanDesc }}</p>
                            </div>

                            {{-- Footer --}}
                            <div class="career-card-footer">
                                <span class="career-posted-date">
                                    <i class="fa-regular fa-clock" aria-hidden="true"></i>
                                    {{ $job->created_at->diffForHumans() }}
                                </span>
                                <a href="{{ route('careers.show', $job->getSlug()) }}" class="career-view-link">
                                    {{ __('View & Apply') }}
                                    <span class="career-view-icon" aria-hidden="true">
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </span>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if($jobs->hasPages())
                    <div class="careers-pagination">
                        {{ $jobs->links() }}
                    </div>
                @endif
            @endif

        </div>
    </section>
</x-layouts.public>
