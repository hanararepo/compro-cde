<x-layouts.admin title="Media Gallery">
    <div x-data="liveTable('{{ route('admin.galleries.index') }}', { search: '{{ request('search') }}', category: '{{ request('category') }}', status: '{{ request('status') }}', author: '{{ request('author') }}' })" class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Media Gallery</h2>
                <p class="text-sm text-slate-500 mt-1">Upload and manage image assets with bilingual titles and approval workflow.</p>
            </div>
            <div class="flex items-center gap-3">
                @can('galleries.approve')
                    <a href="{{ route('admin.galleries.pending') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-amber-200 bg-amber-50 hover:bg-amber-100 text-amber-800 text-sm font-semibold transition-all">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Approval Queue</span>
                    </a>
                @endcan

                <a href="{{ route('admin.gallery-categories.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-semibold transition-all">
                    <span>Manage Categories</span>
                </a>

                @can('galleries.create')
                    <a href="{{ route('admin.galleries.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm shadow-brand-600/30 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Upload Image</span>
                    </a>
                @endcan
            </div>
        </div>

        <!-- Filter & Instant Live Search Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[220px]">
                    <svg class="w-4 h-4 absolute left-3.5 top-3 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input 
                        type="text" 
                        x-model="filters.search" 
                        placeholder="Type to search gallery images instantly..." 
                        class="w-full pl-10 pr-10 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 bg-slate-50/50"
                    >
                    <div x-show="isLoading" class="absolute right-3.5 top-3" style="display: none;">
                        <svg class="animate-spin h-4 w-4 text-brand-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>

                <select x-model="filters.category" class="px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->getTranslation('name', 'en') }}</option>
                    @endforeach
                </select>

                <select x-model="filters.status" class="px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                    <option value="">All Statuses</option>
                    @foreach(\App\Enums\GalleryStatus::cases() as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </select>

                <!-- Author / Uploader Filter (only visible if user has permission to view others) -->
                @if((auth()->user()->can('galleries.view-others') || auth()->user()->hasRole('Administrator')) && isset($authors) && $authors->isNotEmpty())
                    <select x-model="filters.author" class="px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                        <option value="">All Authors</option>
                        @foreach($authors as $author)
                            <option value="{{ $author->id }}">{{ $author->name }}</option>
                        @endforeach
                    </select>
                @endif

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

        <!-- Live Gallery Grid Container -->
        <div id="live-table-container">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @forelse($galleries as $gallery)
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden group hover:shadow-md transition-shadow">
                        <div class="aspect-4/3 overflow-hidden bg-slate-100 relative">
                            <img src="{{ $gallery->thumbnailUrl() }}" alt="{{ $gallery->alt_text ?? '' }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute top-2 left-2 flex flex-col gap-1">
                                @if($gallery->category)
                                    <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-900/80 text-white backdrop-blur-xs">
                                        {{ $gallery->category->getTranslation('name', 'en') }}
                                    </span>
                                @endif
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold shadow-xs {{ $gallery->status->badgeClass() }}">
                                    {{ $gallery->status->label() }}
                                </span>
                            </div>
                        </div>
                        <div class="p-4 space-y-2">
                            <h3 class="font-bold text-slate-900 text-sm truncate">{{ $gallery->getTranslation('title', 'en') }}</h3>
                            <p class="text-xs text-slate-400 truncate">ID: {{ $gallery->getTranslation('title', 'id') }}</p>

                            @if($gallery->rejection_reason && $gallery->status === \App\Enums\GalleryStatus::Rejected)
                                <p class="text-xs text-rose-600 bg-rose-50 p-1.5 rounded-lg border border-rose-100 truncate" title="{{ $gallery->rejection_reason }}">
                                    <strong>Rejected:</strong> {{ $gallery->rejection_reason }}
                                </p>
                            @endif

                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                                <span class="text-slate-400 truncate max-w-[150px]" title="{{ $gallery->uploader?->name }}">{{ $gallery->uploader?->name ?? 'Unknown' }} • {{ $gallery->created_at->format('M d') }}</span>
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    @can('update', $gallery)
                                        <a href="{{ route('admin.galleries.edit', $gallery) }}"
                                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-brand-200 bg-brand-50 hover:bg-brand-100 text-brand-700 hover:text-brand-900 text-[11px] font-semibold transition-all shadow-xs">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </a>
                                    @endcan
                                    @can('delete', $gallery)
                                        <form action="{{ route('admin.galleries.destroy', $gallery) }}" method="POST" class="inline-flex" onsubmit="return confirm('Delete this gallery image?');">
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
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white rounded-2xl p-12 text-center border border-slate-200/80">
                        <p class="text-sm text-slate-400">No gallery images found matching your filters.</p>
                        @can('galleries.create')
                            <a href="{{ route('admin.galleries.create') }}" class="mt-2 inline-block text-xs font-semibold text-brand-600 hover:underline">Upload your first image</a>
                        @endcan
                    </div>
                @endforelse
            </div>

            @if($galleries->hasPages())
                <div class="mt-6">
                    {{ $galleries->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
