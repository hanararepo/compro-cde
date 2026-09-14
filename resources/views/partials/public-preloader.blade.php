@if(request()->routeIs('home'))
<div class="preloader overflow-hidden" role="status" aria-label="{{ __('Loading...') }}">
    <div class="site-name" aria-hidden="true"><span>{{ $siteName }}</span></div>
    <div class="preloader-gutters" aria-hidden="true">
        @for ($bar = 0; $bar < 8; $bar++)
            <div class="bar">
                <div class="inner-bar"></div>
            </div>
        @endfor
    </div>
</div>
<noscript>
    <style>.preloader { display: none; }</style>
</noscript>
@endif
