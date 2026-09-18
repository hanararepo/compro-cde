@php
    $missions = [
        __('Providing innovative, safe, and sustainable energy products, services, and solutions to meet the world\'s evolving needs.'),
        __('Investing strategically in value-added sectors that strengthen energy security and drive national economic growth.'),
        __('Improving the well-being of local communities through job creation, skills training, and empowerment programs.'),
        __('Maintaining environmental sustainability by implementing environmentally friendly technologies and principles of social responsibility.'),
        __('Building a corporate culture based on integrity, team unity, compliance, and synergy to develop a resilient and dedicated team.'),
    ];
    $missionIcons = [
        'M13 2 4 14h7l-1 8 10-12h-7l1-8Z',
        'M4 19h16M5 15l5-5 4 3 6-8M15 5h5v5',
        'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75M13 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z',
        'M20 3c-8-1-15 3-15 10a6 6 0 0 0 6 6c7 0 10-8 9-16ZM3 21l11-11',
        'M12 3 3 7v5c0 5 9 9 9 9s9-4 9-9V7l-9-4ZM8 12l3 3 5-6',
    ];
@endphp

<section id="home-vision-mission" class="home-vision-mission" aria-labelledby="home-vision-mission-heading">
    <div class="container container-2">
        <div class="home-purpose-heading section-heading slide-anim" data-scroll-repeat data-direction="bottom">
            <h4 class="sub-heading">{{ __('About Us') }}</h4>
            <{{ $headingTag ?? 'h2' }} id="home-vision-mission-heading" class="section-title">{{ __('Our Vision &') }} <span>{{ __('Mission') }}</span></{{ $headingTag ?? 'h2' }}>
        </div>

        <div class="home-purpose-grid">
            <article class="home-vision-card slide-anim" data-scroll-repeat aria-labelledby="home-vision-heading" data-direction="left">
                <img class="home-vision-image" src="{{ asset('assets/img/bg-img/vision-mining-sky.webp') }}" alt="" width="1122" height="1402" loading="lazy" decoding="async">
                <div class="home-vision-caption" aria-hidden="true"><span></span> {{ __('North Bengkulu, Bengkulu, Indonesia') }}</div>
                <div class="home-vision-copy">
                    <span class="home-vision-rule" aria-hidden="true"></span>
                    <h3 id="home-vision-heading" class="home-vision-heading">{{ __('Our Vision') }}</h3>
                    <p class="home-vision-statement">{{ __('To become a global force that provides sustainable energy and investment solutions, creates national prosperity, advances society and preserves the earth.') }}</p>
                </div>
            </article>
            <div class="home-mission-panel">
                <h3 id="home-mission-heading" class="home-mission-heading">{{ __('Our Mission') }}</h3>
                <ol class="home-mission-list" aria-labelledby="home-mission-heading" role="list">
                    @foreach ($missions as $mission)
                        <li class="home-mission-item slide-anim" data-scroll-repeat data-direction="right" data-offset="32" data-delay="{{ $loop->index * 0.04 }}">
                            <span class="home-mission-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $missionIcons[$loop->index] }}"/></svg></span>
                            <div class="home-mission-content">
                                <p>{{ $mission }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</section>
