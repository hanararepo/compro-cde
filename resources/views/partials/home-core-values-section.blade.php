@php
    $coreValues = [
        [
            'letter' => 'S',
            'title' => 'Sustainability',
            'icon' => 'M20 4C12 2 4 6 4 13a7 7 0 0 0 7 7c7 0 10-8 9-16ZM4 20l10-10',
            'description' => __('Maintaining environmental sustainability and resources.'),
        ],
        [
            'letter' => 'U',
            'title' => 'Unity',
            'icon' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75M13 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z',
            'description' => __('Strengthening togetherness, cohesion, and loyalty within the organization.'),
        ],
        [
            'letter' => 'C',
            'title' => 'Contribution',
            'icon' => 'M12 21s-9-5.5-9-12a5 5 0 0 1 9-3 5 5 0 0 1 9 3c0 6.5-9 12-9 12ZM8 12h8M12 8v8',
            'description' => __('Providing real benefits to society and the country.'),
        ],
        [
            'letter' => 'C',
            'title' => 'Compliance',
            'icon' => 'M12 3 3 7v5c0 5 9 9 9 9s9-4 9-9V7l-9-4ZM8 12l3 3 5-6',
            'description' => __('Comply with all laws, regulations and industry standards.'),
        ],
        [
            'letter' => 'E',
            'title' => 'Excellence',
            'icon' => 'm12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z',
            'description' => __('Pursuing the best quality and performance without compromise.'),
        ],
        [
            'letter' => 'S',
            'title' => 'Synergy',
            'icon' => 'm10 13 4-4M8 16l-1 1a4 4 0 0 1-6-6l4-4a4 4 0 0 1 6 0M16 8l1-1a4 4 0 0 1 6 6l-4 4a4 4 0 0 1-6 0',
            'description' => __('Creating productive collaborations with partners and stakeholders.'),
        ],
        [
            'letter' => 'S',
            'title' => 'Standards',
            'icon' => 'M8 3h8v4H8V3ZM8 5H5v16h14V5h-3M8 12h8M8 16h5',
            'description' => __('Conduct operations according to the highest mining, safety and operational standards.'),
        ],
    ];
@endphp

<section id="home-core-values" class="home-core-values" aria-labelledby="home-core-values-heading">
    <div class="container container-2">
        <div class="home-values-heading slide-anim" data-scroll-repeat data-offset="32" data-duration="0.85">
            <div class="section-heading mb-0">
                <h4 class="sub-heading">{{ __('Our Culture') }}</h4>
                <h2 id="home-core-values-heading" class="section-title">{{ __('Core') }} <span>{{ __('Values') }}</span></h2>
            </div>
            <div class="home-values-wordmark" aria-hidden="true">SUCCESS<span></span></div>
        </div>

        <ul class="home-values-grid" role="list" tabindex="0" aria-labelledby="home-core-values-heading">
            @foreach ($coreValues as $value)
                <li class="home-value-item slide-anim" data-scroll-repeat data-direction="bottom" data-offset="36" data-duration="0.85" data-delay="{{ $loop->index * 0.07 }}">
                    <div class="home-value-card">
                        <div class="home-value-top">
                            <span class="home-value-letter" aria-hidden="true">{{ $value['letter'] }}</span>
                            <svg class="home-value-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $value['icon'] }}"/></svg>
                        </div>
                        <div class="home-value-content">
                            <h3>{{ $value['title'] }}</h3>
                            <p>{{ $value['description'] }}</p>
                        </div>
                        <span class="home-value-accent" aria-hidden="true"></span>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</section>
