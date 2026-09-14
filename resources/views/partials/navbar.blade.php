<header class="h-16 bg-white border-b border-slate-200 px-4 sm:px-6 lg:px-8 flex items-center justify-between shrink-0">
    <div class="flex items-center gap-4">
        <h1 class="text-xl font-bold text-slate-800 tracking-tight">
            {{ $headerTitle ?? 'Content Management' }}
        </h1>
    </div>

    <div class="flex items-center gap-4">
        <!-- User Dropdown (Alpine.js) -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" @click.outside="open = false" type="button" class="flex items-center gap-3 focus:outline-none">
                <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}" class="w-9 h-9 rounded-full object-cover ring-2 ring-brand-500/20 shadow-sm">
                <div class="hidden sm:block text-left">
                    <p class="text-sm font-semibold text-slate-700 leading-none">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ auth()->user()->roles->pluck('name')->first() ?? 'Member' }}</p>
                </div>
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <!-- Dropdown Menu -->
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-100" 
                 x-transition:enter-start="transform opacity-0 scale-95" 
                 x-transition:enter-end="transform opacity-100 scale-100" 
                 x-transition:leave="transition ease-in duration-75" 
                 x-transition:leave-start="transform opacity-100 scale-100" 
                 x-transition:leave-end="transform opacity-0 scale-95" 
                 class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-100 py-1.5 z-50 focus:outline-none"
                 style="display: none;">
                
                <div class="px-4 py-2 border-b border-slate-100">
                    <p class="text-xs text-slate-400 font-medium">Signed in as</p>
                    <p class="text-xs font-semibold text-slate-800 truncate">{{ auth()->user()->email }}</p>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span>Sign Out</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
