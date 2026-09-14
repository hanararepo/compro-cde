@php
    $coreValues = [
        [
            'letter' => 'S',
            'title' => 'Sustainability',
            'description' => __('Maintaining environmental sustainability and resources.'),
        ],
        [
            'letter' => 'U',
            'title' => 'Unity',
            'description' => __('Strengthening togetherness, cohesion, and loyalty within the organization.'),
        ],
        [
            'letter' => 'C',
            'title' => 'Contribution',
            'description' => __('Providing real benefits to society and the country.'),
        ],
        [
            'letter' => 'C',
            'title' => 'Compliance',
            'description' => __('Comply with all laws, regulations and industry standards.'),
        ],
        [
            'letter' => 'E',
            'title' => 'Excellence',
            'description' => __('Pursuing the best quality and performance without compromise.'),
        ],
        [
            'letter' => 'S',
            'title' => 'Synergy',
            'description' => __('Creating productive collaborations with partners and stakeholders.'),
        ],
        [
            'letter' => 'S',
            'title' => 'Standards',
            'description' => __('Conduct operations according to the highest mining, safety and operational standards.'),
        ],
    ];
@endphp

<section id="home-core-values" class="service-section-2 home-core-values pt-130 pb-130" aria-labelledby="home-core-values-heading">
    <div class="container container-2">
        <div class="row section-heading-wrap ml-0 mw-100 slide-anim" data-scroll-repeat>
            <div class="shape"><img src="{{ asset('assets/img/shapes/section-heading.png') }}" alt="" aria-hidden="true"></div>
            <div class="col-lg-4 col-md-12">
                <div class="section-heading mb-0">
                    <h4 class="sub-heading">{{ __('Our Culture') }}</h4>
                </div>
            </div>
            <div class="col-lg-8 col-md-12">
                <div class="section-heading section-heading-2 mb-0">
                    <h2 id="home-core-values-heading" class="section-title cursor-effect title-2">{{ __('Core') }} <span>{{ __('Values') }}</span></h2>
                </div>
            </div>
        </div>

        <ul class="home-values-grid" role="list">
            @foreach ($coreValues as $value)
                <li class="service-item-2 home-value-item slide-anim" data-scroll-repeat data-delay="{{ $loop->index * 0.06 }}">
                    <span class="home-value-letter" aria-hidden="true">{{ $value['letter'] }}</span>
                    <div class="service-content">
                        <h3 class="title">{{ $value['title'] }}</h3>
                        <p>{{ $value['description'] }}</p>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</section>
