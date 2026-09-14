<!-- Desktop Navbar Menu Items -->
<div class="header-menu-wrap">
    <div class="mobile-menu-items">
        <ul>
            <li class="{{ request()->routeIs('home') ? 'active' : '' }}">
                <a href="{{ route('home') }}">{{ __('Home') }}</a>
            </li>
            <li class="menu-item-has-children {{ request()->routeIs('about.*') ? 'active' : '' }}" data-nav-item="about">
                <a href="javascript:void(0);" role="button" aria-expanded="false" style="cursor: default;" onclick="event.preventDefault();">{{ __('About Us') }}</a>
                <ul class="sub-menu">
                    @foreach([
                        'about.introduction' => __('Introduction'),
                        'about.vision-mission' => __('Vision & Mission'),
                        'about.awards-certificates' => __('Awards & Certification'),
                        'about.corporate-logo' => __('Corporate Logo'),
                        'about.photo-gallery' => __('Photo Gallery'),
                    ] as $menuRoute => $menuLabel)
                        <li class="{{ request()->routeIs($menuRoute) ? 'active' : '' }}">
                            <a href="{{ route($menuRoute) }}" @if(request()->routeIs($menuRoute)) aria-current="page" @endif>{{ $menuLabel }}</a>
                        </li>
                    @endforeach
                </ul>
            </li>
            <li class="{{ $navigationCoalProducts->isNotEmpty() ? 'menu-item-has-children' : '' }} {{ request()->routeIs('coal-products.*') ? 'active' : '' }}" data-nav-item="coal-products">
                <a href="javascript:void(0);" role="button" @if($navigationCoalProducts->isNotEmpty()) aria-expanded="false" @endif style="cursor: default;" onclick="event.preventDefault();">{{ __('Coal Products') }}</a>
                @if($navigationCoalProducts->isNotEmpty())
                    <ul class="sub-menu">
                        @foreach($navigationCoalProducts as $navigationProduct)
                            @php
                                $routeProduct = request()->route('coalProduct');
                                $isCurrentProduct = ($routeProduct instanceof \App\Models\CoalProduct)
                                    ? $routeProduct->id === $navigationProduct->id
                                    : ($routeProduct === $navigationProduct->slug || $routeProduct === (string) $navigationProduct->id);
                            @endphp
                            <li class="{{ $isCurrentProduct ? 'active' : '' }}">
                                <a href="{{ route('coal-products.show', ['coalProduct' => $navigationProduct->slug]) }}"
                                   @if($isCurrentProduct) aria-current="page"@endif>{{ $navigationProduct->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </li>
            <li class="{{ $navigationCategories->isNotEmpty() ? 'menu-item-has-children' : '' }} {{ request()->routeIs('news.*') ? 'active' : '' }}" data-nav-item="news">
                <a href="javascript:void(0);" role="button" @if($navigationCategories->isNotEmpty()) aria-expanded="false" @endif style="cursor: default;" onclick="event.preventDefault();">{{ __('News') }}</a>
                @if($navigationCategories->isNotEmpty())
                    <ul class="sub-menu">
                        @foreach($navigationCategories as $navigationCategory)
                            <li class="{{ request()->route('category')?->id === $navigationCategory->id ? 'active' : '' }}">
                                <a href="{{ route('news.category', ['category' => $navigationCategory->slug]) }}"
                                   @if(request()->route('category')?->id === $navigationCategory->id) aria-current="page" @endif>{{ $navigationCategory->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </li>
            <li class="{{ request()->routeIs('careers.*') ? 'active' : '' }}">
                <a href="{{ route('careers.index') }}">{{ __('Careers') }}</a>
            </li>
            <li class="{{ request()->routeIs('contact') ? 'active' : '' }}">
                <a href="{{ route('contact') }}">{{ __('Contact Us') }}</a>
            </li>
        </ul>
    </div>
</div>
<!-- /.header-menu-wrap -->
