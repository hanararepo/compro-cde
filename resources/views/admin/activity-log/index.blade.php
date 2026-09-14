<x-layouts.admin title="Activity Log">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Activity Log</h2>
                <p class="text-sm text-slate-500 mt-1">Audit trail of all CMS actions — CRUD, approvals, and authentication events.</p>
            </div>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span>{{ $logs->total() }} total entries</span>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <form method="GET" action="{{ route('admin.activity-log.index') }}" class="flex flex-wrap items-end gap-3">
                {{-- Search --}}
                <div class="flex-1 min-w-48">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search description…"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 bg-slate-50">
                </div>

                {{-- Event --}}
                <div class="min-w-36">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Event</label>
                    <select name="event" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 bg-slate-50">
                        <option value="">All Events</option>
                        @foreach($events as $event)
                            <option value="{{ $event }}" @selected(request('event') === $event)>
                                {{ ucfirst(str_replace('_', ' ', $event)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Subject Type --}}
                <div class="min-w-36">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Module</label>
                    <select name="subject_type" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 bg-slate-50">
                        <option value="">All Modules</option>
                        @foreach($subjectTypes as $type)
                            <option value="{{ $type }}" @selected(request('subject_type') === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- User --}}
                <div class="min-w-36">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">User</label>
                    <select name="user_id" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 bg-slate-50">
                        <option value="">All Users</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Date From --}}
                <div class="min-w-36">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 bg-slate-50">
                </div>

                {{-- Date To --}}
                <div class="min-w-36">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 bg-slate-50">
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit"
                            class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg shadow-sm shadow-brand-600/30 transition-all">
                        Filter
                    </button>
                    @if(request()->hasAny(['search','event','subject_type','user_id','date_from','date_to']))
                        <a href="{{ route('admin.activity-log.index') }}"
                           class="px-4 py-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-sm font-medium rounded-lg transition-all">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            @if($logs->isEmpty())
                <div class="flex flex-col items-center justify-center py-20 text-slate-400">
                    <svg class="w-12 h-12 mb-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="font-medium">No activity found</p>
                    <p class="text-sm mt-1">Try adjusting your filters</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-40">Timestamp</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">User</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-28">Event</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-28">Module</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Description</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-32">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($logs as $log)
                                @php
                                    $colour = $log->eventColour();
                                    $colourMap = [
                                        'brand' => 'bg-brand-50 text-brand-700 ring-1 ring-brand-200',
                                        'brand'  => 'bg-brand-50 text-brand-700 ring-1 ring-brand-200',
                                        'rose'    => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
                                        'teal'    => 'bg-teal-50 text-teal-700 ring-1 ring-teal-200',
                                        'amber'   => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
                                        'sky'     => 'bg-sky-50 text-sky-700 ring-1 ring-sky-200',
                                        'slate'   => 'bg-slate-100 text-slate-600 ring-1 ring-slate-200',
                                    ];
                                    $badgeCss = $colourMap[$colour] ?? $colourMap['slate'];
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-5 py-3.5 text-slate-500 tabular-nums text-xs whitespace-nowrap">
                                        <div>{{ $log->created_at->format('d M Y') }}</div>
                                        <div class="text-slate-400">{{ $log->created_at->format('H:i:s') }}</div>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span class="font-medium text-slate-800">{{ $log->user->name }}</span>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold {{ $badgeCss }}">
                                            {{ ucfirst(str_replace('_', ' ', $log->event)) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-500 text-xs">
                                        {{ $log->subjectTypeLabel() }}
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-700 max-w-md">
                                        <div>{{ $log->description }}</div>
                                        @if($log->subject_label)
                                            <div class="text-xs text-slate-400 mt-0.5">{{ $log->subject_label }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-400 text-xs font-mono whitespace-nowrap">
                                        {{ $log->ip_address ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($logs->hasPages())
                    <div class="px-5 py-4 border-t border-slate-100">
                        {{ $logs->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-layouts.admin>
