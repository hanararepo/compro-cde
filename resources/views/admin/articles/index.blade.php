<x-layouts.admin title="Articles">
    <div x-data="liveTable('{{ route('admin.articles.index') }}', { search: '{{ request('search') }}', status: '{{ request('status') }}', category: '{{ request('category') }}', author: '{{ request('author') }}' })" class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Articles Management</h2>
                <p class="text-sm text-slate-500 mt-1">Manage multilingual articles, approval workflow, and publishing status.</p>
            </div>
            <div class="flex items-center gap-3">
                @if(auth()->user()->can('articles.approve') || auth()->user()->hasRole('Administrator'))
                    <a href="{{ route('admin.articles.pending') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-amber-300 bg-amber-50 hover:bg-amber-100 text-amber-900 text-sm font-semibold transition-all">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Approval Queue</span>
                    </a>
                @endif
                <a href="{{ route('admin.articles.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm shadow-brand-600/30 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Write Article</span>
                </a>
            </div>
        </div>

        <!-- Filter & Instant Live Search Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex flex-wrap items-center gap-3">
                <!-- Search Input with Debounce & Spinner -->
                <div class="relative flex-1 min-w-[220px]">
                    <svg class="w-4 h-4 absolute left-3.5 top-3 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input 
                        type="text" 
                        x-model="filters.search" 
                        placeholder="Type to search articles instantly by title or excerpt..." 
                        class="w-full pl-10 pr-10 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 bg-slate-50/50"
                    >
                    <div x-show="isLoading" class="absolute right-3.5 top-3" style="display: none;">
                        <svg class="animate-spin h-4 w-4 text-brand-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Status Filter -->
                <select x-model="filters.status" class="px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                    <option value="">All Statuses</option>
                    @foreach(\App\Enums\ArticleStatus::cases() as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </select>

                <!-- Category Filter -->
                <select x-model="filters.category" class="px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->getTranslation('name', 'en') }}</option>
                    @endforeach
                </select>

                <!-- Author Filter (only visible if user has permission to view others) -->
                @if((auth()->user()->can('articles.view-others') || auth()->user()->hasRole('Administrator')) && isset($authors) && $authors->isNotEmpty())
                    <select x-model="filters.author" class="px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                        <option value="">All Authors</option>
                        @foreach($authors as $author)
                            <option value="{{ $author->id }}">{{ $author->name }}</option>
                        @endforeach
                    </select>
                @endif

                <!-- Reset Button -->
                <button 
                    type="button" 
                    x-show="hasActiveFilters()" 
                    @click="resetFilters()" 
                    class="px-3 py-2 text-xs font-semibold text-rose-600 hover:text-rose-800 transition-colors"
                    style="display: none;"
                >
                    Reset Filters
                </button>
            </div>
        </div>

        <!-- Live Articles Table Container -->
        <div id="live-table-container">
            <x-data-table :headers="['Article', 'Category', 'Author', 'Status', 'Views', 'Date', 'Actions']" :paginator="$articles">
                @forelse($articles as $article)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-6 py-4 flex items-center gap-3 max-w-xs">
                            @if($article->thumbnail)
                                <img src="{{ $article->thumbnailUrl() }}" alt="" class="w-12 h-12 rounded-xl object-cover ring-1 ring-slate-200 shrink-0">
                            @else
                                <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                            <div class="min-w-0">
                                <p class="font-bold text-slate-900 text-sm truncate">{{ $article->getTranslation('title', 'en') }}</p>
                                <p class="text-xs text-slate-400 truncate mt-0.5">ID: {{ $article->getTranslation('title', 'id') }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($article->category)
                                <span class="text-xs px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 font-medium">
                                    {{ $article->category->getTranslation('name', 'en') }}
                                </span>
                            @else
                                <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-600">
                            {{ $article->author->name }}
                        </td>
                        <td class="px-6 py-4">
                            <x-badge :color="$article->status->value">
                                {{ $article->status->label() }}
                            </x-badge>
                        </td>
                        <td class="px-6 py-4 text-xs font-mono text-slate-600">
                            {{ number_format($article->views_count) }}
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-500">
                            {{ $article->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1.5 flex-wrap">

                                {{-- Preview --}}
                                <a href="{{ route('admin.articles.preview', $article) }}" target="_blank"
                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 hover:text-slate-900 text-[11px] font-semibold transition-all shadow-xs">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Preview
                                </a>

                                {{-- Edit --}}
                                @can('update', $article)
                                    <a href="{{ route('admin.articles.edit', $article) }}"
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-brand-200 bg-brand-50 hover:bg-brand-100 text-brand-700 hover:text-brand-900 text-[11px] font-semibold transition-all shadow-xs">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </a>
                                @endcan

                                {{-- Unpublish --}}
                                @if($article->status === \App\Enums\ArticleStatus::Published && (auth()->user()->can('articles.approve') || auth()->user()->hasRole('Administrator')))
                                    <form action="{{ route('admin.articles.unpublish', $article) }}" method="POST" class="inline-flex"
                                          onsubmit="return confirm('Unpublish this article?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-slate-500 hover:text-slate-800 text-[11px] font-semibold transition-all shadow-xs">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                            </svg>
                                            Unpublish
                                        </button>
                                    </form>
                                @endif

                                {{-- Delete --}}
                                @can('delete', $article)
                                    <form action="{{ route('admin.articles.destroy', $article) }}" method="POST" class="inline-flex"
                                          onsubmit="return confirm('Delete this article?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-600 hover:text-rose-800 text-[11px] font-semibold transition-all shadow-xs">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Delete
                                        </button>
                                    </form>
                                @endcan

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-sm text-slate-400">
                            No articles found matching your filters.
                        </td>
                    </tr>
                @endforelse
            </x-data-table>
        </div>
    </div>
</x-layouts.admin>
