<x-layouts.admin title="Career / Job Postings">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Career Management</h2>
                <p class="text-sm text-slate-500 mt-1">Manage job postings visible to the public.</p>
            </div>
            @can('create', \App\Models\JobPosting::class)
                <a href="{{ route('admin.careers.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm shadow-brand-600/30 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>New Job Posting</span>
                </a>
            @endcan
        </div>

        {{-- Filters --}}
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <form method="GET" action="{{ route('admin.careers.index') }}" class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-48">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search job title…"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 bg-slate-50">
                </div>
                <div class="min-w-36">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Type</label>
                    <select name="type" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 bg-slate-50">
                        <option value="">All Types</option>
                        @foreach(\App\Enums\JobType::cases() as $type)
                            <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-36">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 bg-slate-50">
                        <option value="">All</option>
                        <option value="1" @selected(request('status') === '1')>Active</option>
                        <option value="0" @selected(request('status') === '0')>Inactive</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg shadow-sm shadow-brand-600/30 transition-all">
                        Filter
                    </button>
                    @if(request()->hasAny(['search','type','status']))
                        <a href="{{ route('admin.careers.index') }}" class="px-4 py-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-sm font-medium rounded-lg transition-all">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            @if($jobs->isEmpty())
                <div class="flex flex-col items-center justify-center py-20 text-slate-400">
                    <svg class="w-12 h-12 mb-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <p class="font-medium">No job postings found</p>
                    <p class="text-sm mt-1">Create your first job posting to get started.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Job Title</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-28">Type</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-24">Status</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-36">Date</th>
                                <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider w-56">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($jobs as $job)
                                <tr class="hover:bg-slate-50/60 transition-colors align-middle">

                                    {{-- Job Title --}}
                                    <td class="px-5 py-3.5">
                                        <div class="font-semibold text-slate-900 leading-snug">{{ $job->getTranslation('title', 'en', false) }}</div>
                                        <div class="text-xs text-slate-400 mt-0.5 leading-snug">{{ $job->getTranslation('title', 'id', false) }}</div>
                                    </td>

                                    {{-- Type --}}
                                    <td class="px-5 py-3.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold {{ $job->type->badgeClass() }}">
                                            {{ $job->type->label() }}
                                        </span>
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-5 py-3.5">
                                        @if($job->is_active)
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span> Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400 shrink-0"></span> Inactive
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Date --}}
                                    <td class="px-5 py-3.5 tabular-nums whitespace-nowrap">
                                        <div class="text-xs font-medium text-slate-600">{{ $job->created_at->format('d M Y') }}</div>
                                        <div class="text-xs text-slate-400">{{ $job->created_at->format('H:i') }}</div>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-5 py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            @can('viewAny', \App\Models\JobApplication::class)
                                                <a href="{{ route('admin.careers.applications.index', $job) }}"
                                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200/80 transition-colors whitespace-nowrap"
                                                   title="View Applicants">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                    {{ $job->applications_count }} Applicants
                                                </a>
                                            @endcan
                                            <a href="{{ route('careers.show', $job->getSlug()) }}" target="_blank"
                                               class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200 transition-colors"
                                               title="Preview public page">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                </svg>
                                                Preview
                                            </a>
                                            @can('update', $job)
                                                <a href="{{ route('admin.careers.edit', $job) }}"
                                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-brand-50 text-brand-700 hover:bg-brand-100 border border-brand-200/80 transition-colors"
                                                   title="Edit">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                    Edit
                                                </a>
                                            @endcan
                                            @can('delete', $job)
                                                <form method="POST" action="{{ route('admin.careers.destroy', $job) }}" class="inline"
                                                      onsubmit="return confirm('Delete this job posting? All applications will also be removed.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition-colors"
                                                            title="Delete">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                        Delete
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

                @if($jobs->hasPages())
                    <div class="px-5 py-4 border-t border-slate-100">
                        {{ $jobs->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-layouts.admin>
