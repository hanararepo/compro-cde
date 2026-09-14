<x-layouts.admin title="Gallery Videos">
    <div class="space-y-6" x-data="{ videoModalOpen: false, modalVideoId: '' }">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Gallery Videos</h2>
                <p class="text-sm text-slate-500 mt-1">Manage YouTube video embeds with bilingual titles for the public gallery.</p>
            </div>
            @can('gallery-videos.create')
                <a href="{{ route('admin.gallery-videos.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm shadow-brand-600/30 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Add Video</span>
                </a>
            @endcan
        </div>

        {{-- Filters --}}
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <form method="GET" action="{{ route('admin.gallery-videos.index') }}" class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-48">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search by video title or URL…"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 bg-slate-50">
                </div>
                <div class="min-w-40">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 bg-slate-50">
                        <option value="">All Status</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                        <option value="trashed" @selected(request('status') === 'trashed')>Deleted</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg shadow-sm shadow-brand-600/30 transition-all">
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.gallery-videos.index') }}" class="px-4 py-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-sm font-medium rounded-lg transition-all">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Flash Message --}}
        @if(session('success'))
            <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
                <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Video List --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            @if($videos->isEmpty())
                <div class="flex flex-col items-center justify-center py-20 text-slate-400">
                    <div class="w-14 h-14 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-500 mb-4">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="font-semibold text-slate-700">No gallery videos found</p>
                    <p class="text-sm mt-1 text-slate-400">Add your first YouTube video to feature in the gallery.</p>
                    @can('gallery-videos.create')
                        <a href="{{ route('admin.gallery-videos.create') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold transition-all">
                            Add Video
                        </a>
                    @endcan
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-12">#</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-36">Video</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Title</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-20">Order</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-28">Status</th>
                                <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider w-40">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($videos as $video)
                                <tr class="hover:bg-slate-50/60 transition-colors {{ $video->trashed() ? 'opacity-50' : '' }}">
                                    <td class="px-5 py-4 text-slate-400 text-xs tabular-nums">{{ $video->sort_order }}</td>
                                    
                                    {{-- Video Thumbnail with Play Button --}}
                                    <td class="px-5 py-4">
                                        <button type="button"
                                                @click="modalVideoId = '{{ $video->youtube_id }}'; videoModalOpen = true"
                                                class="relative group block w-28 h-16 rounded-xl overflow-hidden bg-slate-900 shadow-xs focus:outline-none ring-1 ring-slate-200 hover:ring-rose-400 transition-all">
                                            <img src="{{ $video->thumbnailUrl() }}"
                                                 alt="{{ $video->getTranslation('title', 'en', false) }}"
                                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                            <div class="absolute inset-0 bg-black/30 group-hover:bg-black/10 transition-colors flex items-center justify-center">
                                                <span class="w-7 h-7 rounded-full bg-rose-600 group-hover:bg-rose-500 text-white flex items-center justify-center shadow-md transition-transform group-hover:scale-110">
                                                    <svg class="w-3.5 h-3.5 fill-current ml-0.5" viewBox="0 0 24 24">
                                                        <path d="M8 5v14l11-7z"/>
                                                    </svg>
                                                </span>
                                            </div>
                                        </button>
                                    </td>

                                    {{-- Titles & URL --}}
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-1.5 font-semibold text-slate-900 truncate max-w-md">
                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-brand-50 text-brand-700">EN</span>
                                            <span class="truncate">{{ $video->getTranslation('title', 'en', false) ?: '—' }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-1 truncate max-w-md">
                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-50 text-amber-700">ID</span>
                                            <span class="truncate">{{ $video->getTranslation('title', 'id', false) ?: '—' }}</span>
                                        </div>
                                        <div class="flex items-center gap-1 text-xs text-slate-400 mt-1">
                                            <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                            </svg>
                                            <a href="{{ $video->youtube_url }}" target="_blank" class="hover:text-rose-600 underline font-mono text-[11px] truncate max-w-xs">
                                                {{ $video->youtube_id ? "youtu.be/{$video->youtube_id}" : $video->youtube_url }}
                                            </a>
                                        </div>
                                    </td>

                                    {{-- Order --}}
                                    <td class="px-5 py-4 tabular-nums text-slate-600 text-sm font-medium">
                                        {{ $video->sort_order }}
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-5 py-4">
                                        @if($video->trashed())
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-700 border border-rose-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Deleted
                                            </span>
                                        @elseif($video->is_active)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactive
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            @unless($video->trashed())
                                                {{-- Toggle Active --}}
                                                @can('gallery-videos.edit')
                                                    <form method="POST" action="{{ route('admin.gallery-videos.toggle-active', $video) }}" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit"
                                                                title="{{ $video->is_active ? 'Deactivate' : 'Activate' }}"
                                                                class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-semibold border transition-colors
                                                                       {{ $video->is_active
                                                                            ? 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100'
                                                                            : 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' }}">
                                                            @if($video->is_active)
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                </svg>
                                                            @else
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                </svg>
                                                            @endif
                                                        </button>
                                                    </form>
                                                @endcan

                                                {{-- Edit --}}
                                                @can('gallery-videos.edit')
                                                    <a href="{{ route('admin.gallery-videos.edit', $video) }}"
                                                       class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-brand-50 text-brand-700 hover:bg-brand-100 border border-brand-200/80 transition-colors">
                                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                        Edit
                                                    </a>
                                                @endcan

                                                {{-- Delete --}}
                                                @can('gallery-videos.delete')
                                                    <form method="POST" action="{{ route('admin.gallery-videos.destroy', $video) }}" class="inline"
                                                          onsubmit="return confirm('Delete this video from gallery?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition-colors">
                                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                            Delete
                                                        </button>
                                                    </form>
                                                @endcan
                                            @endunless
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($videos->hasPages())
                    <div class="px-5 py-4 border-t border-slate-100">
                        {{ $videos->links() }}
                    </div>
                @endif
            @endif
        </div>

        {{-- Video Player Modal --}}
        <div x-show="videoModalOpen"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/75 backdrop-blur-xs"
             @keydown.escape.window="videoModalOpen = false; modalVideoId = ''">
            <div class="relative w-full max-w-3xl bg-slate-900 rounded-2xl overflow-hidden shadow-2xl border border-slate-800"
                 @click.outside="videoModalOpen = false; modalVideoId = ''">
                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-800 bg-slate-950">
                    <span class="text-xs font-semibold text-slate-300">YouTube Player Preview</span>
                    <button type="button" @click="videoModalOpen = false; modalVideoId = ''"
                            class="text-slate-400 hover:text-white text-sm p-1 rounded-lg hover:bg-slate-800 transition-colors">
                        ✕
                    </button>
                </div>
                <div class="aspect-video w-full bg-black">
                    <template x-if="videoModalOpen && modalVideoId">
                        <iframe :src="'https://www.youtube.com/embed/' + modalVideoId + '?autoplay=1'"
                                class="w-full h-full border-0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen></iframe>
                    </template>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
