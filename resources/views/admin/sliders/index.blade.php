<x-layouts.admin title="Hero Sliders">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Hero Sliders</h2>
                <p class="text-sm text-slate-500 mt-1">Manage hero banners for the homepage with desktop & mobile versions.</p>
            </div>
            @can('sliders.create')
                <a href="{{ route('admin.sliders.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm shadow-brand-600/30 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Add Slider</span>
                </a>
            @endcan
        </div>

        {{-- Filters --}}
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <form method="GET" action="{{ route('admin.sliders.index') }}" class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-48">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search slider title…"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 bg-slate-50">
                </div>
                <div class="min-w-40">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 bg-slate-50">
                        <option value="">All</option>
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
                        <a href="{{ route('admin.sliders.index') }}" class="px-4 py-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-sm font-medium rounded-lg transition-all">
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

        {{-- Table --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            @if($sliders->isEmpty())
                <div class="flex flex-col items-center justify-center py-20 text-slate-400">
                    <svg class="w-12 h-12 mb-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="font-medium">No sliders yet</p>
                    <p class="text-sm mt-1">Add your first slider to get started.</p>
                    @can('sliders.create')
                        <a href="{{ route('admin.sliders.create') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold transition-all">
                            Add Slider
                        </a>
                    @endcan
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-10">#</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-28">Preview</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Title</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-20">Order</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-28">Status</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-28">Mobile</th>
                                <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider w-44">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($sliders as $slider)
                                <tr class="hover:bg-slate-50/60 transition-colors {{ $slider->trashed() ? 'opacity-50' : '' }}">
                                    <td class="px-5 py-4 text-slate-400 text-xs tabular-nums">{{ $slider->sort_order }}</td>
                                    <td class="px-5 py-4">
                                        <img src="{{ $slider->desktopImageUrl() }}"
                                             alt="{{ $slider->getTranslation('title', 'en', false) }}"
                                             class="w-24 h-14 object-cover rounded-lg ring-1 ring-slate-200">
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-1.5 font-semibold text-slate-900 truncate max-w-xs">
                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-brand-50 text-brand-700">EN</span>
                                            <span class="truncate">{{ $slider->getTranslation('title', 'en', false) ?: '—' }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-1 truncate max-w-xs">
                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-50 text-amber-700">ID</span>
                                            <span class="truncate">{{ $slider->getTranslation('title', 'id', false) ?: '—' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 tabular-nums text-slate-600 text-sm font-medium">
                                        {{ $slider->sort_order }}
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($slider->trashed())
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-700 border border-rose-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Deleted
                                            </span>
                                        @elseif($slider->is_active)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($slider->image_mobile)
                                            <img src="{{ $slider->mobileImageUrl() }}"
                                                 alt="mobile"
                                                 class="w-10 h-16 object-cover rounded-lg ring-1 ring-slate-200">
                                        @else
                                            <span class="text-xs text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            @unless($slider->trashed())
                                                {{-- Toggle Active --}}
                                                @can('sliders.edit')
                                                    <form method="POST" action="{{ route('admin.sliders.toggle-active', $slider) }}" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit"
                                                                title="{{ $slider->is_active ? 'Deactivate' : 'Activate' }}"
                                                                class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-semibold border transition-colors
                                                                       {{ $slider->is_active
                                                                            ? 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100'
                                                                            : 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' }}">
                                                            @if($slider->is_active)
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
                                                @can('sliders.edit')
                                                    <a href="{{ route('admin.sliders.edit', $slider) }}"
                                                       class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-brand-50 text-brand-700 hover:bg-brand-100 border border-brand-200/80 transition-colors">
                                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                        Edit
                                                    </a>
                                                @endcan

                                                {{-- Delete --}}
                                                @can('sliders.delete')
                                                    <form method="POST" action="{{ route('admin.sliders.destroy', $slider) }}" class="inline"
                                                          onsubmit="return confirm('Delete this slider? Images will also be removed.')">
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

                @if($sliders->hasPages())
                    <div class="px-5 py-4 border-t border-slate-100">
                        {{ $sliders->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-layouts.admin>
