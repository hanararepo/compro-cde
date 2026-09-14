@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="pagination flex items-center justify-between gap-4 pt-4">
        {{-- Mobile View: Simple Previous / Next --}}
        <div class="flex items-center justify-between w-full sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center px-4 py-2 text-xs font-semibold text-slate-400 bg-slate-50 border border-slate-200 rounded-xl cursor-not-allowed">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-brand-50 hover:text-brand-700 hover:border-brand-200 transition-all shadow-xs">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            <span class="text-xs text-slate-500 font-medium">
                Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-brand-50 hover:text-brand-700 hover:border-brand-200 transition-all shadow-xs">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span class="inline-flex items-center px-4 py-2 text-xs font-semibold text-slate-400 bg-slate-50 border border-slate-200 rounded-xl cursor-not-allowed">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>

        {{-- Desktop View --}}
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-xs sm:text-sm text-slate-500">
                    {!! __('Showing') !!}
                    @if ($paginator->firstItem())
                        <span class="font-semibold text-slate-700">{{ $paginator->firstItem() }}</span>
                        {!! __('to') !!}
                        <span class="font-semibold text-slate-700">{{ $paginator->lastItem() }}</span>
                    @else
                        <span class="font-semibold text-slate-700">{{ $paginator->count() }}</span>
                    @endif
                    {!! __('of') !!}
                    <span class="font-semibold text-slate-700">{{ $paginator->total() }}</span>
                    {!! __('results') !!}
                </p>
            </div>

            <div class="inline-flex items-center gap-1.5">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                        <span class="inline-flex items-center justify-center w-9 h-9 text-slate-300 bg-slate-50/80 border border-slate-200/80 rounded-xl cursor-not-allowed text-sm" aria-hidden="true">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </span>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center w-9 h-9 text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-brand-50 hover:text-brand-700 hover:border-brand-300 text-sm font-medium transition-all shadow-xs" aria-label="{{ __('pagination.previous') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span aria-disabled="true" class="inline-flex items-center justify-center min-w-[36px] h-9 px-2 text-slate-400 font-bold text-sm select-none">
                            {{ $element }}
                        </span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page">
                                    <span class="inline-flex items-center justify-center min-w-[36px] h-9 px-3 rounded-xl bg-brand-600 text-white font-bold text-sm shadow-sm shadow-brand-600/30 cursor-default">
                                        {{ $page }}
                                    </span>
                                </span>
                            @else
                                <a href="{{ $url }}" class="inline-flex items-center justify-center min-w-[36px] h-9 px-3 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-brand-50 hover:text-brand-700 hover:border-brand-300 font-semibold text-sm transition-all shadow-xs" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center w-9 h-9 text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-brand-50 hover:text-brand-700 hover:border-brand-300 text-sm font-medium transition-all shadow-xs" aria-label="{{ __('pagination.next') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @else
                    <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                        <span class="inline-flex items-center justify-center w-9 h-9 text-slate-300 bg-slate-50/80 border border-slate-200/80 rounded-xl cursor-not-allowed text-sm" aria-hidden="true">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </span>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
