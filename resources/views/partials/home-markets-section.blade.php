@php
    // Locations identify countries; the connecting arcs are illustrative, not shipping routes.
    $marketCountries = [
        ['code' => 'IDN', 'name' => 'Indonesia', 'lon' => 103, 'lat' => -3.8],
        ['code' => 'CHN', 'name' => 'China', 'lon' => 104, 'lat' => 35],
        ['code' => 'IND', 'name' => 'India', 'lon' => 79, 'lat' => 22],
        ['code' => 'PAK', 'name' => 'Pakistan', 'lon' => 69, 'lat' => 30],
        ['code' => 'BGD', 'name' => 'Bangladesh', 'lon' => 90.3, 'lat' => 24],
        ['code' => 'KOR', 'name' => 'South Korea', 'lon' => 128, 'lat' => 36],
        ['code' => 'TWN', 'name' => 'Taiwan', 'lon' => 121, 'lat' => 24],
        ['code' => 'THA', 'name' => 'Thailand', 'lon' => 101, 'lat' => 16],
        ['code' => 'KHM', 'name' => 'Cambodia', 'lon' => 105, 'lat' => 12.8],
        ['code' => 'VNM', 'name' => 'Vietnam', 'lon' => 108, 'lat' => 17],
        ['code' => 'MYS', 'name' => 'Malaysia', 'lon' => 102, 'lat' => 4],
        ['code' => 'PHL', 'name' => 'Philippines', 'lon' => 123, 'lat' => 12],
    ];
@endphp

<section id="home-markets" class="home-markets-section" aria-labelledby="home-markets-heading" data-market-network data-region-label="{{ __('Asia') }}">
    <div class="container container-2">
        <div class="home-markets-layout">
            <div class="home-markets-copy">
                <div class="section-heading">
                    <h4 class="sub-heading">{{ __('Our Footprint. Your Opportunity.') }}</h4>
                    <h2 id="home-markets-heading" class="section-title">{{ __('Strategic Coverage') }}<br><span>{{ __('Across Key Asian Markets') }}</span></h2>
                </div>
                <p class="home-markets-description">{!! __('We operate and deliver value across <strong>:count key countries</strong> in Asia, connecting resources, markets, and opportunities.', ['count' => 12]) !!}</p>
                <div class="home-markets-stats">
                    <div><strong>12</strong><span>{{ __('Countries connected') }}</span></div>
                    <div><strong>01</strong><span>{{ __('Connected region') }}</span></div>
                </div>
                <p class="home-markets-hint" id="home-markets-hint">{{ __('Explore a country to see its connection.') }}</p>
                <div class="home-markets-countries" role="group" aria-label="{{ __('Explore our markets') }}" aria-describedby="home-markets-hint">
                    @foreach ($marketCountries as $country)
                        <button type="button" class="home-market-button" data-market="{{ $country['code'] }}" data-market-name="{{ __($country['name']) }}" aria-pressed="false">
                            <span class="home-market-dot" aria-hidden="true"></span>
                            <span>{{ __($country['name']) }}</span>
                            @if ($country['code'] === 'IDN')<span class="home-market-origin">{{ __('Origin') }}</span>@endif
                        </button>
                    @endforeach
                </div>
            </div>

            <figure class="home-markets-atlas" aria-label="{{ __('Connections from Indonesia to Asian markets') }}">
            
                <svg class="home-markets-map" viewBox="0 0 850 700" aria-hidden="true" focusable="false">
                    <defs>
                        <pattern id="market-map-grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 H 0 V 40" fill="none" stroke="#a4d5b6" stroke-opacity=".07"/></pattern>
                        <radialGradient id="market-map-glow"><stop stop-color="#337849" stop-opacity=".35"/><stop offset="1" stop-color="#102c23" stop-opacity="0"/></radialGradient>
                    </defs>
                    <rect width="850" height="700" fill="url(#market-map-grid)"/>
                    <ellipse cx="440" cy="410" rx="410" ry="340" fill="url(#market-map-glow)"/>
                    <g class="market-map-land">@include('partials.home-market-map')</g>
                    <g class="market-map-routes">
                        @foreach ($marketCountries as $country)
                            @continue($country['code'] === 'IDN')
                            @php
                                $x = ($country['lon'] - 60) * 10;
                                $y = (55 - $country['lat']) * 10;
                                $curve = 'M 430 588 Q '.(($x + 430) / 2 + 55).' '.(($y + 588) / 2 - 85).' '.$x.' '.$y;
                            @endphp
                            <g data-map-route="{{ $country['code'] }}" class="market-map-route" style="--route-delay: -{{ $loop->index * 0.7 }}s">
                                <path class="market-route-line" d="{{ $curve }}"/>
                                <path class="market-route-flow" d="{{ $curve }}" pathLength="100"/>
                            </g>
                        @endforeach
                    </g>
                    @foreach ($marketCountries as $country)
                        @php $x = ($country['lon'] - 60) * 10; $y = (55 - $country['lat']) * 10; @endphp
                        <g data-map-point="{{ $country['code'] }}" class="market-map-point {{ $country['code'] === 'IDN' ? 'is-origin' : '' }}" transform="translate({{ $x }} {{ $y }})">
                            <circle class="market-point-ring" r="12"/>
                            <circle class="market-point-core" r="4.5"/>
                            <text class="market-point-label" y="-20" text-anchor="middle">{{ __($country['name']) }}</text>
                        </g>
                    @endforeach
                    <text class="market-map-ocean" x="120" y="510" text-anchor="middle">{{ __('INDIAN OCEAN') }}</text>
                    <text class="market-map-ocean" x="735" y="430" text-anchor="middle">{{ __('PACIFIC OCEAN') }}</text>
                </svg>
              
            </figure>
        </div>
    </div>
</section>
