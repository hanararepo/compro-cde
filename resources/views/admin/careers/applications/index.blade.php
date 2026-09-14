<x-layouts.admin title="Applications for {{ $career->getTranslation('title', app()->getLocale(), false) ?: $career->getTranslation('title', 'en', false) }}">
    <div class="space-y-6" x-data="{
        showModal: false,
        selected: null,
        openDetail(data) {
            this.selected = data;
            this.showModal = true;
        },
        closeModal() {
            this.showModal = false;
            this.selected = null;
        }
    }">
        {{-- Header & Breadcrumbs --}}
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-500 mb-2">
                <a href="{{ route('admin.careers.index') }}" class="hover:text-brand-600 transition-colors">Careers</a>
                <span>/</span>
                <span class="text-slate-800 font-medium truncate max-w-sm">{{ $career->getTranslation('title', app()->getLocale(), false) ?: $career->getTranslation('title', 'en', false) }}</span>
                <span>/</span>
                <span>Applications</span>
            </nav>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">
                        Applicants for "{{ $career->getTranslation('title', app()->getLocale(), false) ?: $career->getTranslation('title', 'en', false) }}"
                    </h2>
                    <div class="flex items-center gap-3 mt-1.5">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $career->type->badgeClass() }}">
                            {{ $career->type->label() }}
                        </span>
                        <span class="text-xs text-slate-500">
                            Total: <strong>{{ $applications->total() }}</strong> applicant{{ $applications->total() === 1 ? '' : 's' }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.careers.index') }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-xl transition-all">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Careers
                    </a>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <form method="GET" action="{{ route('admin.careers.applications.index', $career) }}" class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-56">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Search Applicants</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search by applicant name or email..."
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 bg-slate-50">
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit"
                            class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg shadow-sm shadow-brand-600/30 transition-all">
                        Filter
                    </button>
                    @if(request()->has('search') && request('search') !== '')
                        <a href="{{ route('admin.careers.applications.index', $career) }}"
                           class="px-4 py-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-sm font-medium rounded-lg transition-all">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            @if($applications->isEmpty())
                <div class="flex flex-col items-center justify-center py-20 text-slate-400">
                    <svg class="w-12 h-12 mb-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <p class="font-medium text-slate-600">No applications received yet</p>
                    <p class="text-sm text-slate-400 mt-1">Applications submitted by visitors will appear here.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Applicant</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Contact Info</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">LinkedIn</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">CV / Resume</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-36">Applied Date</th>
                                <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider w-36">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($applications as $app)
                                @php
                                    $appData = [
                                        'id'               => $app->id,
                                        'name'             => $app->name,
                                        'email'            => $app->email,
                                        'phone'            => $app->phone,
                                        'linkedin'         => $app->linkedin,
                                        'cv_original_name' => $app->cv_original_name,
                                        'cv_url'           => route('admin.careers.applications.cv', [$career, $app]),
                                        'delete_url'       => route('admin.careers.applications.destroy', [$career, $app]),
                                        'created_at'       => $app->created_at->format('d F Y, H:i'),
                                        'created_diff'     => $app->created_at->diffForHumans(),
                                        'ip_address'       => $app->ip_address,
                                    ];
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    {{-- Applicant Name & IP --}}
                                    <td class="px-5 py-4">
                                        <button type="button"
                                                @click="openDetail({{ json_encode($appData) }})"
                                                class="font-semibold text-slate-900 hover:text-indigo-600 transition-colors text-left flex items-center gap-2 group">
                                            <span>{{ $app->name }}</span>
                                            <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-indigo-600 opacity-0 group-hover:opacity-100 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>
                                        @if($app->ip_address)
                                            <div class="text-xs text-slate-400 mt-0.5">IP: {{ $app->ip_address }}</div>
                                        @endif
                                    </td>

                                    {{-- Contact Info --}}
                                    <td class="px-5 py-4">
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-1.5 text-slate-700">
                                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                </svg>
                                                <a href="mailto:{{ $app->email }}" class="text-brand-600 hover:underline">{{ $app->email }}</a>
                                            </div>
                                            <div class="flex items-center gap-1.5 text-slate-600 text-xs">
                                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                </svg>
                                                <span>{{ $app->phone }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- LinkedIn --}}
                                    <td class="px-5 py-4">
                                        @if($app->linkedin)
                                            <a href="{{ $app->linkedin }}" target="_blank" rel="noopener noreferrer"
                                               class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-800 hover:underline bg-blue-50 px-2.5 py-1 rounded-md">
                                                <svg class="w-3.5 h-3.5 shrink-0 fill-current" viewBox="0 0 24 24">
                                                    <path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.46 10.9v8.37H9.2V10.9H6.46M7.83 6.2a1.68 1.68 0 0 0-1.68 1.68c0 .93.75 1.69 1.68 1.69.93 0 1.69-.76 1.69-1.69 0-.93-.76-1.68-1.69-1.68z"/>
                                                </svg>
                                                Profile
                                                <svg class="w-3 h-3 ml-0.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                </svg>
                                            </a>
                                        @else
                                            <span class="text-xs text-slate-400 italic">Not provided</span>
                                        @endif
                                    </td>

                                    {{-- CV Download --}}
                                    <td class="px-5 py-4">
                                        <a href="{{ route('admin.careers.applications.cv', [$career, $app]) }}"
                                           style="background-color: #059669; color: #ffffff;"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg text-white hover:opacity-90 transition-colors shadow-2xs">
                                            <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                            <span class="text-white font-bold">Download CV</span>
                                        </a>
                                        <div class="text-[11px] text-slate-400 mt-1 truncate max-w-44" title="{{ $app->cv_original_name }}">
                                            {{ $app->cv_original_name }}
                                        </div>
                                    </td>

                                    {{-- Applied Date --}}
                                    <td class="px-5 py-4 text-xs text-slate-500 whitespace-nowrap">
                                        <div>{{ $app->created_at->format('M d, Y') }}</div>
                                        <div class="text-slate-400 text-[11px]">{{ $app->created_at->format('H:i') }}</div>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-5 py-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            {{-- Detail Button --}}
                                            <button type="button"
                                                    @click="openDetail({{ json_encode($appData) }})"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors border border-indigo-200/80"
                                                    title="View Details">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                <span>Detail</span>
                                            </button>

                                            {{-- Delete Button --}}
                                            @can('career-applications.delete')
                                                <form action="{{ route('admin.careers.applications.destroy', [$career, $app]) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Are you sure you want to delete this application? The uploaded CV file will also be permanently deleted.')"
                                                      class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                                            title="Delete Application">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($applications->hasPages())
                    <div class="px-5 py-4 border-t border-slate-200">
                        {{ $applications->links() }}
                    </div>
                @endif
            @endif
        </div>

        {{-- ───────────────────────────────────────────────────────────── --}}
        {{-- Interactive Applicant Detail Modal (Alpine.js)               --}}
        {{-- ───────────────────────────────────────────────────────────── --}}
        <div x-show="showModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title" role="dialog" aria-modal="true"
             @keydown.escape.window="closeModal()">

            {{-- Backdrop --}}
            <div x-show="showModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
                 @click="closeModal()"></div>

            {{-- Modal Content Box --}}
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div x-show="showModal"
                     x-transition:enter="ease-out duration-250"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-200"
                     @click.stop>

                    {{-- Modal Header --}}
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-base">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900" id="modal-title">Applicant Profile</h3>
                                <p class="text-xs text-slate-500">
                                    Applied for: <span class="font-medium text-slate-700">{{ $career->getTranslation('title', app()->getLocale(), false) ?: $career->getTranslation('title', 'en', false) }}</span>
                                </p>
                            </div>
                        </div>

                        <button type="button"
                                @click="closeModal()"
                                class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-6 space-y-6" x-if="selected">
                        {{-- Applicant Profile Banner --}}
                        <div class="flex items-center justify-between p-4 rounded-2xl bg-gradient-to-r from-indigo-50/60 via-slate-50 to-white border border-indigo-100/60">
                            <div class="flex items-center gap-3.5">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white font-bold flex items-center justify-center text-lg shadow-md shadow-indigo-600/20"
                                     x-text="selected?.name ? selected.name.substring(0, 2).toUpperCase() : 'AP'">
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-slate-900" x-text="selected?.name"></h4>
                                    <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
                                        <span x-text="selected?.created_diff"></span>
                                        <span>•</span>
                                        <span x-text="selected?.created_at"></span>
                                    </div>
                                </div>
                            </div>

                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $career->type->badgeClass() }}">
                                {{ $career->type->label() }}
                            </span>
                        </div>

                        {{-- Details Grid --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- Email --}}
                            <div class="p-4 rounded-2xl border border-slate-200/80 bg-slate-50/30">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Email Address</span>
                                    <a :href="'mailto:' + selected?.email"
                                       class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">
                                        Send Email →
                                    </a>
                                </div>
                                <div class="flex items-center gap-2 text-sm font-semibold text-slate-900">
                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <a :href="'mailto:' + selected?.email" class="hover:underline truncate" x-text="selected?.email"></a>
                                </div>
                            </div>

                            {{-- Phone --}}
                            <div class="p-4 rounded-2xl border border-slate-200/80 bg-slate-50/30">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Phone Number</span>
                                    <template x-if="selected?.phone">
                                        <a :href="'https://wa.me/' + selected?.phone.replace(/[^0-9]/g, '')"
                                           target="_blank"
                                           class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">
                                            WhatsApp →
                                        </a>
                                    </template>
                                </div>
                                <div class="flex items-center gap-2 text-sm font-semibold text-slate-900">
                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    <a :href="'tel:' + selected?.phone" class="hover:underline" x-text="selected?.phone"></a>
                                </div>
                            </div>

                            {{-- LinkedIn --}}
                            <div class="p-4 rounded-2xl border border-slate-200/80 bg-slate-50/30 sm:col-span-2">
                                <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">LinkedIn Profile</span>
                                <template x-if="selected?.linkedin">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2 text-sm font-semibold text-slate-900 truncate">
                                            <svg class="w-4 h-4 text-blue-600 shrink-0 fill-current" viewBox="0 0 24 24">
                                                <path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.46 10.9v8.37H9.2V10.9H6.46M7.83 6.2a1.68 1.68 0 0 0-1.68 1.68c0 .93.75 1.69 1.68 1.69.93 0 1.69-.76 1.69-1.69 0-.93-.76-1.68-1.69-1.68z"/>
                                            </svg>
                                            <a :href="selected?.linkedin" target="_blank" rel="noopener noreferrer"
                                               class="text-blue-600 hover:underline truncate"
                                               x-text="selected?.linkedin"></a>
                                        </div>
                                        <a :href="selected?.linkedin" target="_blank" rel="noopener noreferrer"
                                           class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 px-3 py-1 rounded-lg transition-colors ml-3 shrink-0">
                                            Open Profile
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </a>
                                    </div>
                                </template>
                                <template x-if="!selected?.linkedin">
                                    <span class="text-xs text-slate-400 italic">No LinkedIn profile provided by the applicant.</span>
                                </template>
                            </div>
                        </div>

                        {{-- CV / Resume Card --}}
                        <div class="p-5 rounded-2xl bg-brand-50/40 border border-brand-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-3.5">
                                <div class="w-12 h-12 rounded-2xl bg-brand-100 text-brand-700 flex items-center justify-center shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs font-semibold text-brand-800 uppercase tracking-wider">Uploaded CV / Resume</div>
                                    <div class="text-sm font-bold text-slate-900 truncate max-w-sm" x-text="selected?.cv_original_name"></div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">Stored in encrypted private storage</div>
                                </div>
                            </div>

                            <a :href="selected?.cv_url"
                               style="background-color: #059669; color: #ffffff;"
                               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-white text-sm font-semibold shadow-sm hover:opacity-90 transition-all shrink-0">
                                <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                <span class="text-white font-bold">Download CV</span>
                            </a>
                        </div>

                        {{-- Metadata --}}
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/60 flex items-center justify-between text-xs text-slate-500">
                            <div>
                                <span>Submission ID: </span>
                                <strong class="text-slate-700 font-mono" x-text="'#' + selected?.id"></strong>
                            </div>
                            <template x-if="selected?.ip_address">
                                <div>
                                    <span>IP Address: </span>
                                    <span class="font-mono text-slate-700" x-text="selected?.ip_address"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-3">
                        <div>
                            @can('career-applications.delete')
                                <form :action="selected?.delete_url"
                                      method="POST"
                                      onsubmit="return confirm('Are you sure you want to permanently delete this application and its CV?')"
                                      class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Delete Application
                                    </button>
                                </form>
                            @endcan
                        </div>

                        <div class="flex items-center gap-2">
                            <a :href="selected?.cv_url"
                               style="background-color: #059669; color: #ffffff;"
                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white hover:opacity-90 shadow-sm transition-all">
                                <svg class="w-3.5 h-3.5 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                <span class="text-white font-bold">Download CV</span>
                            </a>

                            <a :href="'mailto:' + selected?.email"
                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition-all">
                                <svg class="w-3.5 h-3.5 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                Send Email
                            </a>

                            <button type="button"
                                    @click="closeModal()"
                                    class="px-4 py-2 rounded-xl text-xs font-medium text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors">
                                Close
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
