@php
    $missions = [
        __('Providing innovative, safe, and sustainable energy products, services, and solutions to meet the world\'s evolving needs.'),
        __('Investing strategically in value-added sectors that strengthen energy security and drive national economic growth.'),
        __('Improving the well-being of local communities through job creation, skills training, and empowerment programs.'),
        __('Maintaining environmental sustainability by implementing environmentally friendly technologies and principles of social responsibility.'),
        __('Building a corporate culture based on integrity, team unity, compliance, and synergy to develop a resilient and dedicated team.'),
    ];
@endphp

<section id="home-vision-mission" class="process-section-5 home-vision-mission pt-130 pb-130" aria-labelledby="home-vision-mission-heading">
    <div class="container container-2">
        <div class="row section-heading-wrap ml-0 mw-100 slide-anim" data-scroll-repeat>
            <div class="shape"><img src="{{ asset('assets/img/shapes/section-heading.png') }}" alt="" aria-hidden="true"></div>
            <div class="col-lg-4 col-md-12">
                <div class="section-heading mb-0">
                    <h4 class="sub-heading">{{ __('About Us') }}</h4>
                </div>
            </div>
            <div class="col-lg-8 col-md-12">
                <div class="section-heading section-heading-2 mb-0">
                    <{{ $headingTag ?? 'h2' }} id="home-vision-mission-heading" class="section-title cursor-effect title-2">{{ __('Our Vision &') }} <span>{{ __('Mission') }}</span></{{ $headingTag ?? 'h2' }}>
                </div>
            </div>
        </div>

        <div class="row g-5">
            <div class="col-lg-5">
                <article class="home-vision-copy slide-anim" aria-labelledby="home-vision-heading" data-scroll-repeat>
                    <h3 id="home-vision-heading" class="home-vision-heading">{{ __('Our Vision') }}</h3>
                    <p class="home-vision-statement">{{ __('To become a global force that provides sustainable energy and investment solutions, creates national prosperity, advances society and preserves the earth.') }}</p>
                </article>
            </div>
            <div class="col-lg-7">
                <h3 id="home-mission-heading" class="home-mission-heading slide-anim" data-scroll-repeat>{{ __('Our Mission') }}</h3>
                <ol class="process-item-wrap-5 home-mission-list" aria-labelledby="home-mission-heading" role="list">
                    @foreach ($missions as $mission)
                        <li class="process-item-5 slide-anim" data-scroll-repeat data-delay="{{ $loop->index * 0.06 }}">
                            <span class="number" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <div class="content">
                                <p>{{ $mission }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</section>
