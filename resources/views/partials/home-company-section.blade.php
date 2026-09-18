<section id="home-company" class="about-section-7 home-company-section pt-130 pb-130" aria-labelledby="home-company-heading">
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
                    <{{ $headingTag ?? 'h2' }} id="home-company-heading" class="section-title cursor-effect title-2">{{ Str::beforeLast(__('Our Company'), ' ') }} <span>{{ Str::afterLast(__('Our Company'), ' ') }}</span></{{ $headingTag ?? 'h2' }}>
                </div>
            </div>
        </div>

        <div class="row about-wrap-7">
            <div class="col-lg-4">
                <figure class="home-company-visual slide-anim" data-scroll-repeat data-direction="left">
                    <img class="home-company-pit"
                         src="{{ asset('assets/img/bg-img/vision-mine-refined.webp') }}"
                         alt="{{ __('Aerial view of an open-pit coal mine') }}"
                         width="1122" height="1402" loading="lazy" decoding="async">
                </figure>
            </div>
            <div class="col-lg-8">
                <div class="about-content-7">
                    <div class="left-content home-company-copy slide-anim" data-scroll-repeat data-direction="right" data-delay="0.12">
                        <p>{{ __('PT. Cakrawala Dinamika Energi, PT. Cereno Energi Selaras, and PT. Mitra Padjadjaaran Prima are a group of Penanaman Modal Asing (PMA) coal mining company located in North Bengkulu, Bengkulu, Indonesia. We have a total mining concession rights of 6342 Hectares where PT. CDE has 2000 Hectares, PT. CES has 2342 Hectares and PT. MPP has 2000 Hectares respectively.') }}</p>
                        <p>{{ __('PT. CDE commenced its mining operations since 2018 while PT. CES commenced its mining operations in 2019. Our vast experience as a mining contractor during our early years working in both Kalimantan and Bengkulu, have allowed us to manage and operate the coal mines effectively and sustainably.') }}</p>
                        <p>{{ __('Both PT. CDE and PT. CES produces mainly the mid-calorific thermal coal of 4600 GAR (ARB) and 4800 GAR (ARB). Low sulphur content of less than 0.25% and Ash Fusion Temperature (AFT) of above 1300 degrees are some of the uniqueness of our coal.') }}</p>
                        <p>{{ __('Good working principles and strong customers relationships have allowed us to export our thermal coal to both domestically and the international markets throughout Asia, mainly to countries like India, Pakistan, Thailand and Malaysia.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
