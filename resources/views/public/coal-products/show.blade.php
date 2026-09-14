@push('structured-data')
<script type="application/ld+json">
{!! json_encode([
    '@context'        => 'https://schema.org',
    '@type'           => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home',          'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => __('Coal Products'), 'item' => route('coal-products.index')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $coalProduct->name,  'item' => route('coal-products.show', ['coalProduct' => $coalProduct->slug])],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_TAG) !!}
</script>
@endpush

<x-layouts.public
    :title="$coalProduct->name"
    :metaDescription="__('Detailed technical specifications for :product — a high-quality thermal coal product with competitive calorific value and low sulphur content.', ['product' => $coalProduct->name])"
    :canonicalUrl="route('coal-products.show', ['coalProduct' => $coalProduct->slug])"
>
    <div class="home-header-background" aria-hidden="true"></div>

    <!-- Page Header -->
    <section class="coal-hero-section" aria-labelledby="coal-product-heading">
        <div class="container container-2">
            <nav class="news-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
                <a href="{{ route('home') }}">{{ __('Home') }}</a>
                <span aria-hidden="true">/</span>
                <span>{{ __('Coal Products') }}</span>
                <span aria-hidden="true">/</span>
                <span aria-current="page" style="color: #164e39; font-weight: 600;">{{ $coalProduct->name }}</span>
            </nav>

            <div class="coal-hero-content">
                <div class="coal-hero-badge">
                    <span class="badge-dot"></span>
                    <span>{{ __('Thermal Coal Grade') }}</span>
                </div>
                <h1 id="coal-product-heading" class="section-title title-2 cursor-effect">
                    {{ $coalProduct->name }} <span>{{ __('Specifications') }}</span>
                </h1>
                <p class="coal-hero-lead">
                    {{ __('High-grade thermal coal characterised by consistent quality, low sulphur content, and optimal combustion performance for power generation and heavy industrial applications.') }}
                </p>

                @if(isset($navigationCoalProducts) && $navigationCoalProducts->count() > 1)
                    <div class="coal-product-switcher" aria-label="{{ __('Other Products') }}">
                        <span class="switcher-label">{{ __('Product Grade:') }}</span>
                        <div class="switcher-pills">
                            @foreach($navigationCoalProducts as $navProd)
                                <a href="{{ route('coal-products.show', ['coalProduct' => $navProd->slug]) }}"
                                   class="coal-switcher-pill {{ $navProd->id === $coalProduct->id ? 'active' : '' }}"
                                   @if($navProd->id === $coalProduct->id) aria-current="page" @endif>
                                    <i class="fa-solid fa-fire-flame-curved"></i>
                                    <span>{{ $navProd->name }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- Main Content Section -->
    <section class="coal-detail-section" aria-labelledby="coal-specifications-heading">
        <div class="container container-2">

            @php
                $rawSpec = $coalProduct->specifications;

                // Detect new format: { columns: [...], rows: [[...], ...] }
                $isNewFormat = is_array($rawSpec) && isset($rawSpec['columns']) && isset($rawSpec['rows']);

                if ($isNewFormat) {
                    $specColumns = $rawSpec['columns'];
                    $specRows    = $rawSpec['rows'];
                    $rowCount    = count($specRows);
                } else {
                    // Old format: [{parameter, typical, rejection}, ...]
                    $specColumns = [__('Parameter & Basis'), __('Typical Specification'), __('Rejection Limit')];
                    $specRows    = $rawSpec ?? [];
                    $rowCount    = count($specRows);
                }
            @endphp

            <!-- Main Specifications Table Panel -->
            <div class="coal-spec-panel">
                <div class="coal-spec-panel-header">
                    <div class="header-left">
                        <div class="header-badge">
                            <i class="fa-solid fa-certificate"></i>
                            <span>{{ __('Quality Analysis') }}</span>
                        </div>
                        <h2 id="coal-specifications-heading" class="header-title">
                            {{ __('Quality Parameters & Typical Specifications') }}
                        </h2>
                        <p class="header-sub">
                            {{ __('Standard laboratory analysis based on ASTM / ISO test procedures.') }}
                        </p>
                    </div>
                    <div class="header-right">
                        <span class="param-count-badge">
                            <i class="fa-solid fa-list-check"></i>
                            {{ __(':count Parameters', ['count' => $rowCount]) }}
                        </span>
                    </div>
                </div>

                <div class="coal-table-container">
                    <table class="coal-modern-table" style="--col-count: {{ $isNewFormat ? count($specColumns) : 3 }}">
                        <caption class="visually-hidden">{{ __('Product Specifications') }} — {{ $coalProduct->name }}</caption>
                        <thead>
                            <tr>
                                @if($isNewFormat)
                                    @foreach($specColumns as $colIdx => $colName)
                                        <th scope="col" class="{{ $colIdx === 0 ? 'col-param-dynamic' : 'col-value' }}">
                                            {{ $colName }}
                                        </th>
                                    @endforeach
                                @else
                                    <th scope="col" class="col-param">{{ __('Parameter & Basis') }}</th>
                                    <th scope="col" class="col-typical">{{ __('Typical Specification') }}</th>
                                    <th scope="col" class="col-rejection">{{ __('Rejection Limit') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @if($isNewFormat)
                                @foreach($specRows as $row)
                                    <tr>
                                        @foreach($specColumns as $colIdx => $colName)
                                            @php
                                                $cellVal = $row[$colIdx] ?? '';
                                                $isEmpty = trim($cellVal) === '' || $cellVal === '-' || $cellVal === '—';
                                            @endphp
                                            @if($colIdx === 0)
                                                {{-- First column: parameter style with optional basis pill --}}
                                                @php
                                                    $paramName  = $cellVal;
                                                    $paramBasis = null;
                                                    if (preg_match('/^(.*?)\s*\((.*?)\)$/', $cellVal, $m)) {
                                                        $paramName  = trim($m[1]);
                                                        $paramBasis = trim($m[2]);
                                                    }
                                                @endphp
                                                <th scope="row" class="cell-param">
                                                    <div class="param-info-wrapper">
                                                        <span class="param-indicator" aria-hidden="true"></span>
                                                        <span class="param-name">{{ $paramName }}</span>
                                                        @if($paramBasis)
                                                            <span class="basis-pill" title="{{ __('Testing Basis') }}">{{ $paramBasis }}</span>
                                                        @endif
                                                    </div>
                                                </th>
                                            @else
                                                <td class="cell-typical">
                                                    @if($isEmpty)
                                                        <span class="rejection-none" title="{{ __('Not applicable') }}">&mdash;</span>
                                                    @else
                                                        <span class="typical-value">{{ $cellVal }}</span>
                                                    @endif
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                            @else
                                {{-- Old format: backward compatible --}}
                                @foreach($specRows as $row)
                                    @php
                                        $rawParam   = $row['parameter'] ?? '';
                                        $paramName  = $rawParam;
                                        $paramBasis = null;
                                        if (preg_match('/^(.*?)\s*\((.*?)\)$/', $rawParam, $matches)) {
                                            $paramName  = trim($matches[1]);
                                            $paramBasis = trim($matches[2]);
                                        }
                                        $typical      = $row['typical'] ?? '-';
                                        $rejection    = trim($row['rejection'] ?? '-');
                                        $hasRejection = $rejection !== '' && $rejection !== '-' && $rejection !== '—';
                                    @endphp
                                    <tr>
                                        <th scope="row" class="cell-param">
                                            <div class="param-info-wrapper">
                                                <span class="param-indicator" aria-hidden="true"></span>
                                                <span class="param-name">{{ $paramName }}</span>
                                                @if($paramBasis)
                                                    <span class="basis-pill" title="{{ __('Testing Basis') }}">{{ $paramBasis }}</span>
                                                @endif
                                            </div>
                                        </th>
                                        <td class="cell-typical">
                                            <span class="typical-value">{{ $typical }}</span>
                                        </td>
                                        <td class="cell-rejection">
                                            @if($hasRejection)
                                                <span class="rejection-limit-badge">
                                                    <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                                                    <span>{{ $rejection }}</span>
                                                </span>
                                            @else
                                                <span class="rejection-none" title="{{ __('No rejection limit specified') }}">&mdash;</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- Panel Footer -->
                <div class="coal-spec-panel-footer">
                    <div class="footer-note">
                        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                        <span>{{ __('Specifications shown are typical values based on regular certified laboratory analysis. Final specifications for delivery are confirmed via independent inspection (COA) issued by certified surveyors (e.g. Carsurin, Sucofindo, GeoServices).') }}</span>
                    </div>
                    <div class="footer-action">
                        <a href="{{ route('contact') }}" class="coal-inquire-cta">
                            <span>{{ __('Inquire This Product') }}</span>
                            <span class="cta-icon" aria-hidden="true">
                                <i class="fa-solid fa-arrow-right"></i>
                            </span>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </section>
</x-layouts.public>
