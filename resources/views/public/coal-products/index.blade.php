@push('structured-data')
@php
    $coalListSchema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'ItemList',
        'name'            => __('Coal Products'),
        'url'             => route('coal-products.index'),
        'description'     => __('High-quality thermal coal products with consistent specifications for industrial and power generation applications.'),
        'itemListElement' => $products->values()->map(function ($p, $i) {
            return [
                '@type'    => 'ListItem',
                'position' => $i + 1,
                'name'     => $p->name,
                'url'      => route('coal-products.show', ['coalProduct' => $p->slug]),
            ];
        })->all(),
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($coalListSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_TAG) !!}
</script>
@endpush

<x-layouts.public
    :title="__('Coal Products')"
    :metaDescription="__('Explore our high-quality thermal coal products — featuring consistent specifications, low sulphur content, and high ash fusion temperature for industrial and power generation use.')"
    :canonicalUrl="route('coal-products.index')"
>
    <div class="home-header-background" aria-hidden="true"></div>
    <section class="coal-hero-section" aria-labelledby="coal-products-heading">
        <div class="container container-2">
            <nav class="news-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
                <a href="{{ route('home') }}">{{ __('Home') }}</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page" style="color: #164e39; font-weight: 600;">{{ __('Coal Products') }}</span>
            </nav>

            <div class="coal-hero-content">
                <div class="coal-hero-badge">
                    <span class="badge-dot"></span>
                    <span>{{ __('Our Energy Portfolio') }}</span>
                </div>
                <h1 id="coal-products-heading" class="section-title title-2 cursor-effect">
                    {{ __('Coal') }} <span>{{ __('Products') }}</span>
                </h1>
                <p class="coal-hero-lead">{{ __('Explore our coal products and their specifications.') }}</p>
            </div>
        </div>
    </section>
    <section class="coal-products-section" aria-labelledby="coal-products-heading">
        <div class="container container-2">
            @if($products->isNotEmpty())
                <div class="coal-products-grid">
                    @foreach($products as $product)
                        <a class="coal-product-card" href="{{ route('coal-products.show', ['coalProduct' => $product->slug]) }}">
                            <span class="coal-product-number" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <h2>{{ $product->name }}</h2>
                            <p>{{ __(':count parameters', ['count' => isset($product->specifications['rows']) ? count($product->specifications['rows']) : count($product->specifications)]) }}</p>
                            <span class="coal-product-link">{{ __('View Specifications') }} <span aria-hidden="true">&nearr;</span></span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="news-empty"><p>{{ __('Coal product specifications will be available soon.') }}</p></div>
            @endif
        </div>
    </section>
</x-layouts.public>
