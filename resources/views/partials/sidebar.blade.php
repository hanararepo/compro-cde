@php
    $user = auth()->user();
    $pendingCount = \App\Models\Article::where('status', \App\Enums\ArticleStatus::Pending)->count();
    $pendingGalleriesCount = \App\Models\Gallery::where('status', \App\Enums\GalleryStatus::Pending)->count();
    $unreadMessagesCount = \App\Models\ContactMessage::unread()->count();
    $siteName = $settings['site_name'] ?? config('app.name', 'CMS');
    $siteLogo = $settings['site_logo'] ?? '';

    $sidebarTheme = \App\Models\Setting::get('admin_sidebar_theme', 'brand_dark');
    $sidebar = match($sidebarTheme) {
        'white' => [
            'aside'       => 'bg-white text-slate-700 border-r border-slate-200',
            'logo_bar'    => 'bg-white border-b border-slate-200',
            'logo_text'   => 'text-slate-900',
            'header'      => 'text-slate-400',
            'item'        => 'text-slate-600 hover:bg-brand-50 hover:text-brand-700',
            'item_sub'    => 'text-slate-500 hover:bg-brand-50 hover:text-brand-700',
            'active'      => 'bg-brand-600 text-white shadow-sm shadow-brand-600/30',
            'badge'       => 'bg-slate-100 text-slate-600',
            'footer_bar'  => 'border-t border-slate-200',
            'footer_btn'  => 'text-slate-500 bg-slate-100 hover:bg-brand-50 hover:text-brand-700',
        ],
        'dark' => [
            'aside'       => 'bg-slate-900 text-slate-300 border-r border-slate-800',
            'logo_bar'    => 'bg-slate-950/60 border-b border-slate-800',
            'logo_text'   => 'text-white',
            'header'      => 'text-slate-500',
            'item'        => 'text-slate-300 hover:bg-slate-800 hover:text-white',
            'item_sub'    => 'text-slate-400 hover:bg-slate-800 hover:text-white',
            'active'      => 'bg-brand-600 text-white shadow-sm shadow-brand-600/50',
            'badge'       => 'bg-slate-800 text-slate-300',
            'footer_bar'  => 'border-t border-slate-800',
            'footer_btn'  => 'text-slate-400 bg-slate-800/60 hover:bg-slate-800 hover:text-white',
        ],
        default => [ // brand_dark
            'aside'       => 'bg-brand-950 text-brand-100 border-r border-brand-900/60',
            'logo_bar'    => 'bg-brand-950 border-b border-brand-900/60',
            'logo_text'   => 'text-white',
            'header'      => 'text-brand-400',
            'item'        => 'text-brand-200 hover:bg-brand-900 hover:text-white',
            'item_sub'    => 'text-brand-300 hover:bg-brand-900 hover:text-white',
            'active'      => 'bg-brand-600 text-white shadow-sm shadow-brand-600/50',
            'badge'       => 'bg-brand-900/80 text-brand-200',
            'footer_bar'  => 'border-t border-brand-900/60',
            'footer_btn'  => 'text-brand-300 bg-brand-900/60 hover:bg-brand-900 hover:text-white',
        ],
    };
@endphp

<aside x-data="{ open: false }" class="w-64 {{ $sidebar['aside'] }} flex flex-col shrink-0 transition-all duration-200">
    <!-- Brand / Logo Area -->
    <div class="h-16 flex items-center px-6 {{ $sidebar['logo_bar'] }}">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
            @if($siteLogo)
                <img src="{{ $siteLogo }}" alt="{{ $siteName }}" class="h-8 w-auto object-contain">
            @else
                <div class="w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center text-white font-bold shadow-md shadow-brand-500/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
            @endif
            <span class="font-bold text-lg {{ $sidebar['logo_text'] }} tracking-tight">CDE GROUP</span>
        </a>
    </div>

    <!-- Navigation Links -->
    <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
        <!-- Dashboard -->
        <a href="{{ route('admin.dashboard') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.dashboard') ? $sidebar['active'] : $sidebar['item'] }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            <span>Dashboard</span>
        </a>

        <!-- Content Section Header -->
        <div class="pt-4 pb-1 px-3 text-xs font-semibold {{ $sidebar['header'] }} uppercase tracking-wider">Content Management</div>

        <!-- Coal Products -->
        @can('coal-products.view')
            <a href="{{ route('admin.coal-products.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.coal-products.*') ? $sidebar['active'] : $sidebar['item'] }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m12 3 9 5-9 5-9-5 9-5Zm-9 5v8l9 5 9-5V8M12 13v8" />
                </svg>
                <span>Coal Products</span>
            </a>
        @endcan

        <!-- Articles Menu Group -->
        @if($user->can('articles.view') || $user->can('articles.create') || $user->can('articles.edit') || $user->hasRole('Administrator'))
            <a href="{{ route('admin.articles.index') }}"
               class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.articles.index', 'admin.articles.create', 'admin.articles.edit') ? $sidebar['active'] : $sidebar['item'] }}">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                    </svg>
                    <span>Articles</span>
                </div>
            </a>
        @endif

        <!-- Approval Queue -->
        @if($user->can('articles.approve') || $user->hasRole('Administrator'))
            <a href="{{ route('admin.articles.pending') }}"
               class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.articles.pending') ? $sidebar['active'] : $sidebar['item'] }}">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Article Approvals</span>
                </div>
                @if($pendingCount > 0)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                        {{ $pendingCount }}
                    </span>
                @endif
            </a>
        @endif

        <!-- Article Categories -->
        @if($user->can('article-categories.view') || $user->hasRole('Administrator'))
            <a href="{{ route('admin.article-categories.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.article-categories.*') ? $sidebar['active'] : $sidebar['item_sub'] }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
                <span>Article Categories</span>
            </a>
        @endif

        <!-- Article Tags -->
        @if($user->can('article-tags.view') || $user->hasRole('Administrator'))
            <a href="{{ route('admin.article-tags.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.article-tags.*') ? $sidebar['active'] : $sidebar['item_sub'] }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                </svg>
                <span>Article Tags</span>
            </a>
        @endif

        <!-- Hero Sliders -->
        @if($user->hasRole('Administrator') || $user->can('sliders.view'))
            <a href="{{ route('admin.sliders.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.sliders.*') ? $sidebar['active'] : $sidebar['item'] }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/>
                </svg>
                <span>Hero Sliders</span>
            </a>
        @endif

        <!-- Awards & Certificates -->
        @if($user->hasRole('Administrator') || $user->can('awards.view'))
            <a href="{{ route('admin.awards.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.awards.*') ? $sidebar['active'] : $sidebar['item'] }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
                <span>Awards & Certificates</span>
            </a>
        @endif

        <!-- Galleries Menu Group -->
        @if($user->can('galleries.view') || $user->can('galleries.create') || $user->can('galleries.edit') || $user->can('gallery-videos.view') || $user->hasRole('Administrator'))
            <div class="pt-4 pb-1 px-3 text-xs font-semibold {{ $sidebar['header'] }} uppercase tracking-wider">Media Gallery</div>

            <a href="{{ route('admin.galleries.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.galleries.index', 'admin.galleries.create', 'admin.galleries.edit') ? $sidebar['active'] : $sidebar['item'] }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span>Gallery Photos</span>
            </a>

            @if($user->can('gallery-videos.view') || $user->hasRole('Administrator'))
                <a href="{{ route('admin.gallery-videos.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.gallery-videos.*') ? $sidebar['active'] : $sidebar['item'] }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <span>Gallery Videos</span>
                </a>
            @endif

            @if($user->can('galleries.approve') || $user->hasRole('Administrator'))
                <a href="{{ route('admin.galleries.pending') }}"
                   class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.galleries.pending') ? $sidebar['active'] : $sidebar['item'] }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Gallery Approvals</span>
                    </div>
                    @if($pendingGalleriesCount > 0)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                            {{ $pendingGalleriesCount }}
                        </span>
                    @endif
                </a>
            @endif

            <a href="{{ route('admin.gallery-categories.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.gallery-categories.*') ? $sidebar['active'] : $sidebar['item_sub'] }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <span>Gallery Categories</span>
            </a>
        @endif

        <!-- Operations Section -->
        @if($user->can('contact-messages.view') || $user->hasRole('Administrator'))
            <div class="pt-4 pb-1 px-3 text-xs font-semibold {{ $sidebar['header'] }} uppercase tracking-wider">Operations</div>

            <a href="{{ route('admin.contact-messages.index') }}"
               class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.contact-messages.*') ? $sidebar['active'] : $sidebar['item'] }}">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span>Contact Messages</span>
                </div>
                @if($unreadMessagesCount > 0)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-brand-500/20 text-brand-400 border border-brand-500/30">
                        {{ $unreadMessagesCount }}
                    </span>
                @endif
            </a>
        @endif

        <!-- Careers / Job Postings -->
        @if($user->can('careers.view') || $user->hasRole('Administrator'))
            <a href="{{ route('admin.careers.index') }}"
               class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.careers.*') ? $sidebar['active'] : $sidebar['item'] }}">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span>Careers / Jobs</span>
                </div>
            </a>
        @endif

        <!-- System & ACL Section -->
        @if($user->hasRole('Administrator') || $user->can('users.view') || $user->can('roles.view') || $user->can('settings.view'))
            <div class="pt-4 pb-1 px-3 text-xs font-semibold {{ $sidebar['header'] }} uppercase tracking-wider">Administration</div>

            @if($user->can('users.view') || $user->hasRole('Administrator'))
                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.users.*') ? $sidebar['active'] : $sidebar['item'] }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span>Users</span>
                </a>
            @endif

            @if($user->can('roles.view') || $user->hasRole('Administrator'))
                <a href="{{ route('admin.roles.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.roles.*') ? $sidebar['active'] : $sidebar['item'] }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    <span>Roles & ACL</span>
                </a>
            @endif

            @if($user->can('settings.view') || $user->hasRole('Administrator'))
                <a href="{{ route('admin.settings.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.settings.*') ? $sidebar['active'] : $sidebar['item'] }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span>Website Settings</span>
                </a>
            @endif

            @if($user->can('activity-log.view') || $user->hasRole('Administrator'))
                <a href="{{ route('admin.activity-log.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.activity-log.*') ? $sidebar['active'] : $sidebar['item'] }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span>Activity Log</span>
                </a>
            @endif
        @endif
    </nav>

    <!-- Public Website Quicklink -->
    <div class="p-4 {{ $sidebar['footer_bar'] }}">
        <a href="{{ route('home') }}" target="_blank" class="flex items-center justify-center gap-2 w-full py-2 px-3 rounded-lg text-xs font-semibold {{ $sidebar['footer_btn'] }} transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
            </svg>
            <span>Visit Public Site</span>
        </a>
    </div>
</aside>
