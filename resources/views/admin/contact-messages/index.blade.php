<x-layouts.admin title="Contact Messages">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Contact Messages</h2>
                <p class="text-sm text-slate-500 mt-1">Messages received from the public contact form.</p>
            </div>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span>{{ $messages->total() }} total messages</span>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <form method="GET" action="{{ route('admin.contact-messages.index') }}" class="flex flex-wrap items-end gap-3">
                {{-- Search --}}
                <div class="flex-1 min-w-48">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search name, email, subject…"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 bg-slate-50">
                </div>

                {{-- Status --}}
                <div class="min-w-36">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 bg-slate-50">
                        <option value="">All</option>
                        <option value="unread" @selected(request('status') === 'unread')>Unread</option>
                        <option value="read" @selected(request('status') === 'read')>Read</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit"
                            class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg shadow-sm shadow-brand-600/30 transition-all">
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.contact-messages.index') }}"
                           class="px-4 py-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-sm font-medium rounded-lg transition-all">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            @if($messages->isEmpty())
                <div class="flex flex-col items-center justify-center py-20 text-slate-400">
                    <svg class="w-12 h-12 mb-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <p class="font-medium">No messages found</p>
                    <p class="text-sm mt-1">Try adjusting your filters</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-8"></th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Name</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Email</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Subject</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-40">Date</th>
                                <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider w-32">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($messages as $msg)
                                <tr class="hover:bg-slate-50/60 transition-colors {{ !$msg->is_read ? 'bg-brand-50/30' : '' }}">
                                    {{-- Unread Indicator --}}
                                    <td class="px-5 py-3.5">
                                        @if(!$msg->is_read)
                                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-brand-500 shadow-sm shadow-brand-400/50" title="Unread"></span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 {{ !$msg->is_read ? 'font-semibold text-slate-900' : 'text-slate-700' }}">
                                        {{ $msg->name }}
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-500">{{ $msg->email }}</td>
                                    <td class="px-5 py-3.5 {{ !$msg->is_read ? 'font-semibold text-slate-900' : 'text-slate-700' }} max-w-xs truncate">
                                        {{ $msg->subject }}
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-500 tabular-nums text-xs whitespace-nowrap">
                                        <div>{{ $msg->created_at->format('d M Y') }}</div>
                                        <div class="text-slate-400">{{ $msg->created_at->format('H:i') }}</div>
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.contact-messages.show', $msg) }}"
                                               class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-brand-50 text-brand-700 hover:bg-brand-100 transition-colors"
                                               title="View">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                View
                                            </a>
                                            @can('delete', $msg)
                                                <form method="POST" action="{{ route('admin.contact-messages.destroy', $msg) }}" class="inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this message?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-rose-50 text-rose-700 hover:bg-rose-100 transition-colors"
                                                            title="Delete">
                                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

                {{-- Pagination --}}
                @if($messages->hasPages())
                    <div class="px-5 py-4 border-t border-slate-100">
                        {{ $messages->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-layouts.admin>
