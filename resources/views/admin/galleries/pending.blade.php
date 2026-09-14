<x-layouts.admin title="Gallery Approval Queue">
    <div x-data="liveTable('{{ route('admin.galleries.pending') }}', { search: '{{ request('search') }}', category: '{{ request('category') }}' })" class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Gallery Approval Queue</h2>
                <p class="text-sm text-slate-500 mt-1">Review, approve, or reject gallery images submitted by authors.</p>
            </div>
            <a href="{{ route('admin.galleries.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
                ← Back to All Galleries
            </a>
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
                        placeholder="Type to search pending gallery images instantly..." 
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

        <!-- Live Pending Table Container -->
        <div id="live-table-container">
            <x-data-table :headers="['Image', 'Title / Description', 'Category', 'Uploader', 'Submitted Date', 'Review Actions']" :paginator="$galleries">
                @forelse($galleries as $gallery)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-6 py-4">
                            <img src="{{ $gallery->thumbnailUrl() }}" alt="{{ $gallery->alt_text ?? '' }}" class="w-16 h-12 object-cover rounded-lg ring-1 ring-slate-200">
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-bold text-slate-900 text-sm">{{ $gallery->getTranslation('title', 'en') }}</p>
                            <p class="text-xs text-slate-500 mt-0.5 line-clamp-1">ID: {{ $gallery->getTranslation('title', 'id') }}</p>
                            @if($gallery->getTranslation('description', 'en'))
                                <p class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $gallery->getTranslation('description', 'en') }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($gallery->category)
                                <span class="text-xs px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 font-medium">
                                    {{ $gallery->category->getTranslation('name', 'en') }}
                                </span>
                            @else
                                <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs font-semibold text-slate-700">
                            {{ $gallery->uploader->name ?? 'Unknown' }}
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-500">
                            {{ $gallery->created_at->diffForHumans() }}
                        </td>
                        <td class="px-6 py-4" x-data="{ rejectOpen: false }">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <!-- Approve Form -->
                                <form action="{{ route('admin.galleries.approve', $gallery) }}" method="POST" class="inline-flex">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 hover:text-emerald-900 text-[11px] font-semibold transition-all shadow-xs">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Approve
                                    </button>
                                </form>

                                <!-- Reject Button Toggle -->
                                <button type="button" @click="rejectOpen = !rejectOpen"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 hover:text-rose-900 text-[11px] font-semibold transition-all shadow-xs">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Reject
                                </button>
                            </div>

                            <!-- Reject Reason Box (Alpine) -->
                            <div x-show="rejectOpen" class="mt-3 p-3 rounded-xl bg-rose-50/70 border border-rose-200 space-y-2">
                                <form action="{{ route('admin.galleries.reject', $gallery) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="rejection_reason" placeholder="Reason for rejection..." required class="w-full px-3 py-1.5 text-xs bg-white border border-rose-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-500">
                                    <div class="flex justify-end gap-2 mt-2">
                                        <button type="button" @click="rejectOpen = false" class="text-xs text-slate-500 hover:text-slate-800">Cancel</button>
                                        <button type="submit" class="px-2.5 py-1 rounded-md bg-rose-600 text-white text-xs font-semibold">Confirm Rejection</button>
                                    </div>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-400">
                            🎉 There are no pending gallery images awaiting approval!
                        </td>
                    </tr>
                @endforelse
            </x-data-table>
        </div>
    </div>
</x-layouts.admin>
