<x-layouts.public :title="'[PREVIEW] ' . $article->getTranslation('title', app()->getLocale())">
    <div x-data="{ previewLang: '{{ app()->getLocale() }}', rejectOpen: false }">
        <!-- Floating Reviewer Toolbar -->
        <div class="sticky top-20 z-50 bg-slate-900/95 backdrop-blur-md text-white border-y border-slate-700 shadow-xl py-3 px-4 sm:px-8">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
                <!-- Left: Status Indicator -->
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-brand-500/20 text-brand-300 border border-brand-500/30">
                        <span class="w-2 h-2 rounded-full bg-brand-400 animate-pulse"></span>
                        PREVIEW MODE
                    </span>
                    <span class="text-xs text-slate-300">
                        Article Status: <x-badge :color="$article->status->value">{{ $article->status->label() }}</x-badge>
                    </span>
                </div>

                <!-- Center: Language Toggle -->
                <div class="flex items-center gap-2 bg-slate-800 p-1 rounded-xl border border-slate-700">
                    <span class="text-xs text-slate-400 pl-2 font-medium">View in:</span>
                    <button type="button" @click="previewLang = 'en'" :class="previewLang === 'en' ? 'bg-brand-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="px-3 py-1 rounded-lg text-xs transition-all">
                        🇬🇧 English
                    </button>
                    <button type="button" @click="previewLang = 'id'" :class="previewLang === 'id' ? 'bg-brand-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="px-3 py-1 rounded-lg text-xs transition-all">
                        🇮🇩 Bahasa Indonesia
                    </button>
                </div>

                <!-- Right: Action Buttons -->
                <div class="flex items-center gap-3">
                    @can('approve', $article)
                        @if($article->status->value === 'pending')
                            <!-- Approve Form -->
                            <form action="{{ route('admin.articles.approve', $article) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="px-4 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-xs transition-colors">
                                    ✓ Approve & Publish
                                </button>
                            </form>

                            <!-- Reject Toggle -->
                            <button @click="rejectOpen = !rejectOpen" class="px-3 py-1.5 rounded-xl bg-rose-600/80 hover:bg-rose-600 text-white text-xs font-semibold transition-colors">
                                ✗ Reject
                            </button>
                        @endif
                    @endcan

                    @can('update', $article)
                        <a href="{{ route('admin.articles.edit', $article) }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition-colors border border-slate-700">
                            Edit Article
                        </a>
                    @endcan

                    <a href="{{ route('admin.articles.pending') }}" class="text-xs text-slate-400 hover:text-white transition-colors">
                        ← Back to Queue
                    </a>
                </div>
            </div>

            <!-- Reject Reason Drawer -->
            <div x-show="rejectOpen" x-transition class="max-w-7xl mx-auto mt-3 pt-3 border-t border-slate-800">
                <form action="{{ route('admin.articles.reject', $article) }}" method="POST" class="flex flex-col sm:flex-row items-center gap-3">
                    @csrf
                    @method('PATCH')
                    <input type="text" name="rejection_reason" placeholder="Explain why this article is rejected..." required class="flex-1 w-full px-3.5 py-1.5 text-xs bg-slate-800 border border-slate-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-rose-500">
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" @click="rejectOpen = false" class="px-3 py-1.5 text-xs text-slate-400 hover:text-white">Cancel</button>
                        <button type="submit" class="px-4 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold">Confirm Reject</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Article Reader Area -->
        <article class="py-12 bg-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Breadcrumbs -->
                <nav class="flex items-center gap-2 text-xs text-slate-400 mb-8">
                    <span>Home</span>
                    <span>/</span>
                    <span>Articles</span>
                    <span>/</span>
                    @if($article->category)
                        <span class="text-brand-600 font-semibold" x-text="previewLang === 'en' ? '{{ $article->category->getTranslation('name', 'en') }}' : '{{ $article->category->getTranslation('name', 'id') }}'"></span>
                        <span>/</span>
                    @endif
                    <span class="text-slate-600 truncate max-w-xs" x-text="previewLang === 'en' ? '{{ addslashes($article->getTranslation('title', 'en')) }}' : '{{ addslashes($article->getTranslation('title', 'id')) }}'"></span>
                </nav>

                <!-- Article Header -->
                <header class="space-y-4 mb-8">
                    @if($article->category)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-brand-50 text-brand-700" x-text="previewLang === 'en' ? '{{ $article->category->getTranslation('name', 'en') }}' : '{{ $article->category->getTranslation('name', 'id') }}'">
                        </span>
                    @endif

                    <!-- English Title -->
                    <h1 x-show="previewLang === 'en'" class="text-3xl sm:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                        {{ $article->getTranslation('title', 'en') }}
                    </h1>

                    <!-- Indonesian Title -->
                    <h1 x-show="previewLang === 'id'" class="text-3xl sm:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight" style="display: none;">
                        {{ $article->getTranslation('title', 'id') }}
                    </h1>

                    <!-- Author & Publishing Meta -->
                    <div class="flex items-center gap-4 py-4 border-y border-slate-100">
                        <img src="{{ $article->author->avatarUrl() }}" alt="{{ $article->author->name }}" class="w-12 h-12 rounded-full object-cover ring-2 ring-slate-100">
                        <div>
                            <p class="font-bold text-slate-900 text-sm">{{ $article->author->name }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Submitted on {{ $article->created_at->format('F d, Y') }} 
                                • Author Profile
                            </p>
                        </div>
                    </div>
                </header>

                <!-- Featured Thumbnail Image -->
                @if($article->thumbnail)
                    <div class="aspect-16/9 rounded-3xl overflow-hidden mb-10 shadow-lg shadow-slate-100">
                        <img src="{{ $article->thumbnailUrl() }}" alt="" class="w-full h-full object-cover">
                    </div>
                @endif

                <!-- English Content Body -->
                <div x-show="previewLang === 'en'" class="article-content">
                    {!! $article->getTranslation('content', 'en') !!}
                </div>

                <!-- Indonesian Content Body -->
                <div x-show="previewLang === 'id'" class="article-content" style="display: none;">
                    {!! $article->getTranslation('content', 'id') !!}
                </div>

                <!-- Tags -->
                @if($article->tags->isNotEmpty())
                    <div class="mt-12 pt-6 border-t border-slate-100 flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tags:</span>
                        @foreach($article->tags as $tag)
                            <span class="px-3 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-medium">
                                #{{ $tag->getTranslation('name', app()->getLocale()) }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </article>
    </div>
</x-layouts.public>
