<x-layouts.admin title="Message Details">
    <div class="space-y-6 max-w-4xl">
        {{-- Top Navigation & Actions --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.contact-messages.index') }}"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition-colors shadow-2xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Message Details</h2>
                    <p class="text-sm text-slate-500 mt-0.5">Received on {{ $contactMessage->created_at->format('d F Y, H:i') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                {{-- Reply via Email button (mailto) --}}
                @php
                    $replySubject = rawurlencode('Re: ' . $contactMessage->subject);
                    $replyBody = rawurlencode("\n\n---\nOriginal Message from {$contactMessage->name}:\n" . $contactMessage->message);
                    $mailtoLink = "mailto:{$contactMessage->email}?subject={$replySubject}&body={$replyBody}";
                @endphp
                <a href="{{ $mailtoLink }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 shadow-sm shadow-brand-600/30 transition-all hover:shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span>Reply via Email</span>
                </a>

                {{-- Delete button --}}
                @can('delete', $contactMessage)
                    <form method="POST" action="{{ route('admin.contact-messages.destroy', $contactMessage) }}"
                          onsubmit="return confirm('Are you sure you want to delete this message?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>Delete</span>
                        </button>
                    </form>
                @endcan
            </div>
        </div>

        {{-- Message Card --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            {{-- Sender Info Header --}}
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-brand-100 text-brand-700 font-bold flex items-center justify-center text-lg shadow-2xs">
                            {{ strtoupper(substr($contactMessage->name, 0, 2)) }}
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">{{ $contactMessage->name }}</h3>
                            <p class="text-sm text-slate-500">
                                <a href="mailto:{{ $contactMessage->email }}" class="text-brand-600 hover:underline">
                                    {{ $contactMessage->email }}
                                </a>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-600">
                            {{ $contactMessage->created_at->diffForHumans() }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-brand-50 text-brand-700 ring-1 ring-brand-200">
                            Read
                        </span>
                    </div>
                </div>
            </div>

            {{-- Subject & Content --}}
            <div class="p-6 sm:p-8 space-y-6">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Subject</label>
                    <p class="text-lg font-bold text-slate-800">{{ $contactMessage->subject }}</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Message</label>
                    <div class="p-5 rounded-xl bg-slate-50 border border-slate-200/80 text-slate-700 text-sm leading-relaxed whitespace-pre-wrap">
                        {{ $contactMessage->message }}
                    </div>
                </div>
            </div>

            {{-- Footer Action Bar --}}
            <div class="p-4 sm:px-8 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
                <div>
                    IP Address / Tracking: <span class="font-mono text-slate-600">{{ request()->ip() }}</span>
                </div>
                <div>
                    <a href="{{ $mailtoLink }}" class="font-semibold text-brand-600 hover:text-brand-700 hover:underline flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        Open default email client to reply
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
