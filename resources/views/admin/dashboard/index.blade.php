@php
    $user = auth()->user();
@endphp

<x-layouts.admin title="Dashboard">
    <div class="space-y-8">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Dashboard Overview</h2>
                <p class="text-sm text-slate-500 mt-1">Real-time performance metrics, content analytics, and editorial workflow.</p>
            </div>
            @if($user->can('articles.create') || $user->hasRole('Administrator'))
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.articles.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm shadow-brand-600/30 transition-all hover:shadow-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Write Article</span>
                    </a>
                </div>
            @endif
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4">
            {{-- Articles Card --}}
            @if($user->can('articles.view') || $user->can('articles.view-others') || $user->hasRole('Administrator'))
                <x-stat-card 
                    title="{{ $stats['can_view_other_articles'] ? 'Total Articles' : 'My Articles' }}" 
                    value="{{ number_format($stats['total_articles']) }}" 
                    color="brand">
                    <x-slot:icon>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                        </svg>
                    </x-slot:icon>
                </x-stat-card>

                <x-stat-card 
                    title="{{ $stats['can_view_other_articles'] ? 'Total Readers / Views' : 'My Article Views' }}" 
                    value="{{ number_format($stats['total_views']) }}" 
                    color="brand">
                    <x-slot:icon>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </x-slot:icon>
                </x-stat-card>

                <x-stat-card 
                    title="{{ $stats['can_approve_articles'] ? 'Pending Approval' : 'My Pending Articles' }}" 
                    value="{{ $stats['pending_articles'] }}" 
                    color="amber">
                    <x-slot:icon>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </x-slot:icon>
                </x-stat-card>
            @endif

            {{-- Gallery Card --}}
            @if($stats['can_view_galleries'])
                <x-stat-card 
                    title="{{ $user->can('galleries.view-others') || $user->hasRole('Administrator') ? 'Gallery Media' : 'My Uploads' }}" 
                    value="{{ number_format($stats['total_galleries']) }}" 
                    color="blue">
                    <x-slot:icon>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </x-slot:icon>
                </x-stat-card>
            @endif

            {{-- Contact Messages Card --}}
            @if($stats['can_view_contact_messages'])
                <x-stat-card 
                    title="Contact Messages" 
                    value="{{ number_format($stats['total_messages']) }}" 
                    color="purple"
                    :trend="$stats['unread_messages'] > 0 ? $stats['unread_messages'] . ' unread' : null"
                    :trendLabel="$stats['unread_messages'] > 0 ? 'new' : null">
                    <x-slot:icon>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </x-slot:icon>
                </x-stat-card>
            @endif
        </div>

        {{-- Pending Approval Alert Banner --}}
        @if($stats['pending_articles'] > 0)
            @if($stats['can_approve_articles'])
                <div class="bg-amber-50/80 border border-amber-200/80 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-amber-950">
                                {{ $stats['pending_articles'] }} {{ Str::plural('article', $stats['pending_articles']) }} waiting for review
                            </h4>
                            <p class="text-xs text-amber-800/80 mt-0.5">Authors have submitted drafts for approval. Review and approve them to publish live.</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.articles.pending') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white transition-colors shrink-0 shadow-xs">
                        <span>Review Pending Submissions</span>
                        <span>→</span>
                    </a>
                </div>
            @else
                <div class="bg-amber-50/80 border border-amber-200/80 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-amber-950">
                                {{ $stats['pending_articles'] }} {{ Str::plural('article', $stats['pending_articles']) }} awaiting review
                            </h4>
                            <p class="text-xs text-amber-800/80 mt-0.5">Your submitted drafts are currently under editorial review before publication.</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.articles.index', ['status' => 'pending']) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white transition-colors shrink-0 shadow-xs">
                        <span>View My Drafts</span>
                        <span>→</span>
                    </a>
                </div>
            @endif
        @endif

        {{-- Unread Contact Messages Alert Banner (Only for roles with contact-messages.view) --}}
        @if($stats['can_view_contact_messages'] && $stats['unread_messages'] > 0)
            <div class="bg-brand-50/80 border border-brand-200/80 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-brand-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-brand-950">
                            {{ $stats['unread_messages'] }} {{ Str::plural('contact message', $stats['unread_messages']) }} waiting for reply
                        </h4>
                        <p class="text-xs text-brand-800/80 mt-0.5">New inquiries from visitors through the public Contact Us form.</p>
                    </div>
                </div>
                <a href="{{ route('admin.contact-messages.index', ['status' => 'unread']) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-700 text-white transition-colors shrink-0 shadow-xs">
                    <span>View Unread Messages</span>
                    <span>→</span>
                </a>
            </div>
        @endif

        <!-- Visual Analytics Row (Trends & Categories) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Col 1 & 2: Monthly Publishing Trends -->
            <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">Publishing Activity</h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ $stats['can_view_other_articles'] ? 'Articles published over the last 6 months' : 'Your articles published over the last 6 months' }}
                        </p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-100">
                        Last 6 Months
                    </span>
                </div>

                <!-- CSS Bar Chart -->
                <div class="flex items-end justify-between gap-4 h-48 pt-6 pb-2 px-4 border-b border-slate-100">
                    @foreach($stats['monthly_trends'] as $trend)
                        <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end group">
                            <!-- Count Tooltip -->
                            <span class="text-xs font-bold text-slate-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                {{ $trend['count'] }}
                            </span>
                            <!-- Bar -->
                            <div class="w-full max-w-[40px] bg-brand-50 group-hover:bg-brand-600 rounded-t-lg transition-all duration-300 relative flex items-end justify-center"
                                 style="height: {{ $trend['height'] }}%">
                                <div class="w-full bg-brand-500 group-hover:bg-brand-600 rounded-t-lg transition-colors h-full"></div>
                            </div>
                            <!-- Month Label -->
                            <span class="text-xs font-semibold text-slate-500 mt-2">
                                {{ $trend['month'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Col 3: Category Distribution -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">Content by Category</h3>
                        @if($user->can('article-categories.view') || $user->hasRole('Administrator'))
                            <a href="{{ route('admin.article-categories.index') }}" class="text-xs font-semibold text-brand-600 hover:underline">Manage</a>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mb-6">Distribution of published articles</p>

                    <div class="space-y-4">
                        @forelse($stats['category_distribution'] as $cat)
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-semibold text-slate-700 truncate max-w-[140px]">{{ $cat['name'] }}</span>
                                    <span class="text-slate-500 font-medium">{{ $cat['count'] }} articles ({{ $cat['percentage'] }}%)</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500" 
                                         style="width: {{ $cat['percentage'] }}%; background-color: {{ $cat['color'] }}"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 py-6 text-center">No categorized published articles yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100 text-xs text-slate-400 flex items-center justify-between">
                    <span>Active Published</span>
                    <span class="font-bold text-slate-700">{{ $stats['published_articles'] }} total</span>
                </div>
            </div>
        </div>

        {{-- Tables Row (Top Viewed Articles, Recent Articles, & Recent Inquiries) --}}
        <div class="grid grid-cols-1 {{ $stats['can_view_contact_messages'] ? 'lg:grid-cols-3' : 'lg:grid-cols-2' }} gap-6">
            <!-- Top 5 Most Viewed Articles -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">
                            {{ $stats['can_view_other_articles'] ? 'Top Articles' : 'My Top Articles' }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Most read articles by traffic</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="divide-y divide-slate-100">
                        @forelse($stats['top_articles'] as $index => $top)
                            <div class="py-3.5 px-4 flex items-center justify-between gap-3 hover:bg-slate-50/80 transition-colors">
                                <div class="flex items-center gap-3.5 min-w-0 flex-1">
                                    <div class="w-8 h-8 rounded-xl flex items-center justify-center text-xs font-bold shrink-0 shadow-2xs {{ $index === 0 ? 'bg-amber-100 text-amber-900 ring-2 ring-amber-300' : ($index === 1 ? 'bg-slate-100 text-slate-800 ring-1 ring-slate-300' : ($index === 2 ? 'bg-orange-100 text-orange-900 ring-1 ring-orange-300' : 'bg-slate-50 text-slate-600 border border-slate-200')) }}">
                                        {{ $index + 1 }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        @can('update', $top)
                                            <a href="{{ route('admin.articles.edit', $top) }}" class="text-xs font-semibold text-slate-900 hover:text-brand-600 transition-colors truncate block">
                                                {{ $top->getTranslation('title', 'en') }}
                                            </a>
                                        @else
                                            <span class="text-xs font-semibold text-slate-900 truncate block">
                                                {{ $top->getTranslation('title', 'en') }}
                                            </span>
                                        @endcan
                                        <div class="flex items-center gap-1.5 text-[11px] text-slate-500 mt-1">
                                            @if($top->category)
                                                <span class="truncate max-w-[120px]">{{ $top->category->getTranslation('name', 'en') }}</span>
                                                <span>•</span>
                                            @endif
                                            <span>{{ number_format($top->views_count) }} views</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-xs text-slate-400">
                                No published articles with views yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Recent Articles Table -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">
                            {{ $stats['can_view_other_articles'] ? 'Recent Articles' : 'My Recent Articles' }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Latest articles in the system</p>
                    </div>
                    <a href="{{ route('admin.articles.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">View All →</a>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="divide-y divide-slate-100">
                        @forelse($stats['recent_articles'] as $article)
                            <div class="py-3.5 px-4 flex items-center justify-between gap-3 hover:bg-slate-50/80 transition-colors">
                                <div class="min-w-0 flex-1">
                                    @can('update', $article)
                                        <a href="{{ route('admin.articles.edit', $article) }}" class="text-xs font-semibold text-slate-900 hover:text-brand-600 transition-colors truncate block">
                                            {{ $article->getTranslation('title', 'en') }}
                                        </a>
                                    @else
                                        <span class="text-xs font-semibold text-slate-900 truncate block">
                                            {{ $article->getTranslation('title', 'en') }}
                                        </span>
                                    @endcan
                                    <div class="flex items-center gap-1.5 text-[11px] text-slate-400 mt-1">
                                        <span>{{ $article->author->name }}</span>
                                        <span>•</span>
                                        <span>{{ $article->created_at->format('M d, Y') }}</span>
                                    </div>
                                </div>

                                <div class="shrink-0">
                                    <x-badge :color="$article->status->value">
                                        {{ $article->status->label() }}
                                    </x-badge>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-xs text-slate-400">
                                No articles created yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Recent Contact Inquiries (Visible only with contact-messages.view permission) --}}
            @if($stats['can_view_contact_messages'])
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 tracking-tight">Recent Inquiries</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Latest messages from contact form</p>
                        </div>
                        <a href="{{ route('admin.contact-messages.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">View All →</a>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                        <div class="divide-y divide-slate-100">
                            @forelse($stats['recent_messages'] as $message)
                                <div class="py-3.5 px-4 flex items-center justify-between gap-3 hover:bg-slate-50/80 transition-colors {{ !$message->is_read ? 'bg-brand-50/30' : '' }}">
                                    <div class="flex items-center gap-3 min-w-0 flex-1">
                                        <div class="w-8 h-8 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                            {{ strtoupper(substr($message->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-1.5">
                                                @if(!$message->is_read)
                                                    <span class="w-2 h-2 rounded-full bg-brand-500 shrink-0" title="Unread"></span>
                                                @endif
                                                <a href="{{ route('admin.contact-messages.show', $message) }}" class="text-xs font-semibold text-slate-900 hover:text-brand-600 transition-colors truncate block">
                                                    {{ $message->subject }}
                                                </a>
                                            </div>
                                            <div class="flex items-center gap-1.5 text-[11px] text-slate-400 mt-1">
                                                <span class="text-slate-600 font-medium truncate max-w-[100px]">{{ $message->name }}</span>
                                                <span>•</span>
                                                <span>{{ $message->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="shrink-0">
                                        <a href="{{ route('admin.contact-messages.show', $message) }}" class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-brand-50 text-brand-700 hover:bg-brand-100 transition-colors">
                                            View
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center text-xs text-slate-400">
                                    No contact messages received yet.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
