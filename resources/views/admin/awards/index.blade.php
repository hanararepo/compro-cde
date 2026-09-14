<x-layouts.admin title="Awards & Certificates">
    <div x-data="liveTable('{{ route('admin.awards.index') }}', { search: '{{ request('search') }}', type: '{{ request('type') }}', is_active: '{{ request('is_active') }}' })" class="space-y-6">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Awards & Certificates</h2>
                <p class="text-sm text-slate-500 mt-1">Manage company awards and certificates with bilingual content.</p>
            </div>
            <div class="flex items-center gap-3">
                @can('create', \App\Models\Award::class)
                    <a href="{{ route('admin.awards.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm shadow-brand-600/30 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Add New</span>
                    </a>
                @endcan
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[220px]">
                    <svg class="w-4 h-4 absolute left-3.5 top-3 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" x-model="filters.search" placeholder="Search awards & certificates..." class="w-full pl-10 pr-10 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 bg-slate-50/50">
                    <div x-show="isLoading" class="absolute right-3.5 top-3" style="display: none;">
                        <svg class="animate-spin h-4 w-4 text-brand-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>

                <select x-model="filters.type" class="px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                    <option value="">All Types</option>
                    <option value="award">Awards</option>
                    <option value="certificate">Certificates</option>
                </select>

                <select x-model="filters.is_active" class="px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>

                <button type="button" x-show="hasActiveFilters()" @click="resetFilters()" class="px-3 py-2 text-xs font-semibold text-rose-600 hover:text-rose-800 transition-colors" style="display: none;">
                    Reset Filters
                </button>
            </div>
        </div>

        <!-- Live Grid Container -->
        <div id="live-table-container">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @forelse($awards as $award)
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden group hover:shadow-md transition-shadow">
                        <!-- Image -->
                        <div class="aspect-4/3 overflow-hidden bg-slate-100 relative">
                            <img src="{{ $award->imageUrl() }}" alt="{{ $award->getTranslation('title', 'en') }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute top-2 left-2 flex flex-col gap-1">
                                <span class="px-2.5 py-1 rounded-md text-xs font-semibold {{ $award->type === 'award' ? 'bg-amber-500 text-white' : 'bg-sky-500 text-white' }}">
                                    {{ ucfirst($award->type) }}
                                </span>
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ $award->is_active ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                                    {{ $award->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                        <!-- Info -->
                        <div class="p-4 space-y-2">
                            <h3 class="font-bold text-slate-900 text-sm truncate">{{ $award->getTranslation('title', 'en') }}</h3>
                            <p class="text-xs text-slate-400 truncate">ID: {{ $award->getTranslation('title', 'id') ?: '—' }}</p>
                            @if($award->issued_date)
                                <p class="text-[11px] font-medium text-slate-500 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    {{ $award->issued_date->translatedFormat('d M Y') }}
                                </p>
                            @endif

                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                                <span class="text-slate-400">Sort: {{ $award->sort_order }}</span>
                                <div class="flex items-center gap-1.5">
                                    @can('update', $award)
                                        <a href="{{ route('admin.awards.edit', $award) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-brand-200 bg-brand-50 hover:bg-brand-100 text-brand-700 text-[11px] font-semibold transition-all shadow-xs">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </a>
                                    @endcan
                                    @can('delete', $award)
                                        <form action="{{ route('admin.awards.destroy', $award) }}" method="POST" class="inline-flex" onsubmit="return confirm('Delete this {{ $award->type }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-600 text-[11px] font-semibold transition-all shadow-xs">
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
                        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-brand-50 flex items-center justify-center">
                            <svg class="w-8 h-8 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-700 mb-1">No awards or certificates found</p>
                        <p class="text-xs text-slate-400">Try adjusting your filters or add a new entry.</p>
                        @can('create', \App\Models\Award::class)
                            <a href="{{ route('admin.awards.create') }}" class="mt-3 inline-block text-xs font-semibold text-brand-600 hover:underline">Add your first award</a>
                        @endcan
                    </div>
                @endforelse
            </div>

            @if($awards->hasPages())
                <div class="mt-6">
                    {{ $awards->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
