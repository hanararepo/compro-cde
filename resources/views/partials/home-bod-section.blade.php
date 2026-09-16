{{-- Board of Directors Section
     Menggunakan style card dari Antra Pages Team template.
--}}
<section id="home-bod" class="bod-section pt-130 pb-130 overflow-hidden" aria-labelledby="bod-heading">
    <div class="container container-2">

        {{-- Section Heading --}}
        <div class="row section-heading-wrap ml-0 mw-100 slide-anim" data-scroll-repeat>
            <div class="shape">
                <img src="{{ asset('assets/img/shapes/section-heading.png') }}" alt="" aria-hidden="true">
            </div>
            <div class="col-lg-4 col-md-12">
                <div class="section-heading mb-0">
                    <h4 class="sub-heading">{{ __('Our Leadership') }}</h4>
                </div>
            </div>
            <div class="col-lg-8 col-md-12">
                <div class="section-heading section-heading-2 mb-0">
                    <h2 id="bod-heading" class="section-title cursor-effect title-2">
                        {{ Str::beforeLast(__('Board of Directors'), ' ') }} <span>{{ Str::afterLast(__('Board of Directors'), ' ') }}</span>
                    </h2>
                </div>
            </div>
        </div>
        {{-- ./ Section Heading --}}

        {{-- BOD Cards Grid --}}
        <div class="row justify-content-center g-4 bod-grid">

             {{-- BOD Member 1 --}}
            <div class="col-xl-4 col-md-6 slide-anim" data-scroll-repeat data-delay="0.08">
                <div class="bod-card">
                    <div class="bod-img-wrap">
                        <img
                            src="{{ asset('assets/img/images/DAVIDHENDRICKSIREGAR_COO_CDE.png') }}"
                            alt="David Hendrick Siregar"
                            loading="lazy"
                            decoding="async"
                            class="bod-img"
                        >
                        <div class="bod-overlay">
                            <span class="bod-overlay-role">Chief Operating Officer (COO)</span>
                        </div>
                    </div>
                    <div class="bod-info">
                        <h3 class="bod-name">David Hendrick Siregar</h3>
                        <span class="bod-role">Chief Operating Officer (COO)</span>
                    </div>
                </div>
            </div>

            {{-- BOD Member 2 --}}
            <div class="col-xl-4 col-md-6 slide-anim" data-scroll-repeat data-delay="0.0">
                <div class="bod-card">
                    <div class="bod-img-wrap">
                        <img
                            src="{{ asset('assets/img/images/ADITYA_RAHMAN_CEO_CDE.png') }}"
                            alt="Aditya Rahman"
                            loading="lazy"
                            decoding="async"
                            class="bod-img"
                        >
                        <div class="bod-overlay">
                            <span class="bod-overlay-role">Chief Executive Officer (CEO)</span>
                        </div>
                    </div>
                    <div class="bod-info">
                        <h3 class="bod-name">Aditya Rahman</h3>
                        <span class="bod-role">Chief Executive Officer (CEO)</span>
                    </div>
                </div>
            </div>

            {{-- BOD Member 3 --}}
            <div class="col-xl-4 col-md-6 slide-anim" data-scroll-repeat data-delay="0.16">
                <div class="bod-card">
                    <div class="bod-img-wrap">
                        <img
                            src="{{ asset('assets/img/images/AFANSURYADI_CSO_CCM_GROUP.png') }}"
                            alt="Afan Suryadi"
                            loading="lazy"
                            decoding="async"
                            class="bod-img"
                        >
                        <div class="bod-overlay">
                            <span class="bod-overlay-role">Chief Sales Officer (CSO)</span>
                        </div>
                    </div>
                    <div class="bod-info">
                        <h3 class="bod-name">Afan Suryadi</h3>
                        <span class="bod-role">Chief Sales Officer (CSO)</span>
                    </div>
                </div>
            </div>

        </div>
        {{-- ./ BOD Cards Grid --}}

    </div>
</section>
