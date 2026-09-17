<x-layouts.public :title="'[PREVIEW] ' . $article->getTranslation('title', app()->getLocale())">

    @push('head-meta')
    <style>
        /* Sembunyikan navbar & footer di halaman preview */
        .header, .public-company-footer, #scroll-percentage { display: none !important; }

        /* Toolbar styles */
        #preview-bar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 9999;
            background: #111827;
            border-bottom: 1px solid #1f2937;
            box-shadow: 0 1px 12px rgba(0,0,0,0.5);
            font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;
        }
        #preview-bar-main {
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 8px;
            max-width: 1000px; margin: 0 auto; padding: 10px 20px;
        }
        .pb-left  { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .pb-right { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

        .pb-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 3px 10px; border-radius: 100px; font-size: 11px; font-weight: 700;
            letter-spacing: .04em; text-transform: uppercase;
        }
        .pb-badge-preview {
            background: rgba(48,170,71,.12); border: 1px solid rgba(48,170,71,.3); color: #4ade80;
        }
        .pb-badge-dot { width: 6px; height: 6px; border-radius: 50%; background: #22c55e; }

        .pb-status {
            font-size: 12px; color: #9ca3af;
        }
        .pb-status strong { color: #e5e7eb; font-weight: 600; }

        .pb-divider { width: 1px; height: 16px; background: #374151; }

        /* Language toggle */
        .pb-lang { display: flex; align-items: center; gap: 2px; background: #1f2937; border-radius: 8px; padding: 3px; border: 1px solid #374151; }
        .pb-lang-btn {
            padding: 4px 12px; border-radius: 6px; font-size: 12px; font-weight: 500;
            border: 0; background: transparent; cursor: pointer; color: #6b7280; transition: all .15s;
        }
        .pb-lang-btn.active { background: #1d662b; color: #fff; font-weight: 700; }

        /* Action buttons */
        .pb-btn {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 5px 14px; border-radius: 7px; font-size: 12px; font-weight: 600;
            border: 0; cursor: pointer; text-decoration: none; transition: all .15s;
            white-space: nowrap;
        }
        .pb-btn-approve { background: #166534; color: #fff; }
        .pb-btn-approve:hover { background: #15803d; }
        .pb-btn-reject  { background: #991b1b; color: #fff; }
        .pb-btn-reject:hover  { background: #b91c1c; }
        .pb-btn-edit    { background: #1f2937; color: #d1d5db; border: 1px solid #374151; }
        .pb-btn-edit:hover { background: #374151; }
        .pb-btn-back    { background: transparent; color: #6b7280; font-weight: 500; padding: 5px 8px; }
        .pb-btn-back:hover { color: #d1d5db; }

        /* Reject drawer */
        #preview-reject-drawer {
            display: none;
            background: #1f2937; border-top: 1px solid #374151; padding: 12px 20px;
        }
        #preview-reject-drawer form { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; max-width: 1000px; margin: 0 auto; }
        #preview-reject-drawer input {
            flex: 1; min-width: 200px; padding: 7px 12px; font-size: 12px;
            background: #111827; border: 1px solid #374151; border-radius: 7px; color: #e5e7eb;
            outline: none;
        }
        #preview-reject-drawer input:focus { border-color: #4b5563; }
        #preview-reject-drawer input::placeholder { color: #6b7280; }

        /* Article spacing — push content below fixed toolbar */
        #preview-article {
            padding-top: var(--toolbar-h, 60px);
        }
    </style>
    @endpush

    <div x-data="{ previewLang: '{{ app()->getLocale() }}' }">

        {{-- ── Reviewer Toolbar ──────────────────────────────── --}}
        <div id="preview-bar">
            <div id="preview-bar-main">

                {{-- Left: mode + status --}}
                <div class="pb-left">
                    <span class="pb-badge pb-badge-preview">
                        <span class="pb-badge-dot"></span>
                        Preview
                    </span>
                    <span class="pb-status">
                        Status: <strong>{{ $article->status->label() }}</strong>
                    </span>
                    <span class="pb-divider" aria-hidden="true"></span>
                    {{-- Language toggle --}}
                    <div class="pb-lang">
                        <button class="pb-lang-btn" :class="previewLang === 'en' && 'active'" @click="previewLang = 'en'" type="button">EN</button>
                        <button class="pb-lang-btn" :class="previewLang === 'id' && 'active'" @click="previewLang = 'id'" type="button">ID</button>
                    </div>
                </div>

                {{-- Right: actions --}}
                <div class="pb-right">
                    @can('approve', $article)
                        @if($article->status->value === 'pending')
                            <form method="POST" action="{{ route('admin.articles.approve', $article) }}" style="margin:0;">
                                @csrf @method('PATCH')
                                <button type="submit" class="pb-btn pb-btn-approve">✓ Approve</button>
                            </form>
                            <button type="button" class="pb-btn pb-btn-reject" onclick="document.getElementById('preview-reject-drawer').style.display = document.getElementById('preview-reject-drawer').style.display === 'none' ? 'block' : 'none'">✕ Reject</button>
                        @endif
                    @endcan
                    @can('update', $article)
                        <a href="{{ route('admin.articles.edit', $article) }}" class="pb-btn pb-btn-edit">Edit</a>
                    @endcan
                    <a href="{{ route('admin.articles.pending') }}" class="pb-btn pb-btn-back">← Queue</a>
                </div>
            </div>

            {{-- Reject drawer --}}
            <div id="preview-reject-drawer">
                <form method="POST" action="{{ route('admin.articles.reject', $article) }}">
                    @csrf @method('PATCH')
                    <input type="text" name="rejection_reason" placeholder="Reason for rejection..." required>
                    <button type="button" class="pb-btn pb-btn-back" onclick="document.getElementById('preview-reject-drawer').style.display='none'">Cancel</button>
                    <button type="submit" class="pb-btn pb-btn-reject">Confirm Reject</button>
                </form>
            </div>
        </div>

        {{-- ── Article (same structure as public/news/show.blade.php) ── --}}
        <article id="preview-article" class="blog-details-section news-detail-section" style="background:#fff;">
            <div class="container news-detail-container">

                {{-- Breadcrumb --}}
                <nav class="news-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
                    <span>{{ __('Home') }}</span>
                    <span aria-hidden="true">/</span>
                    <span>{{ __('News') }}</span>
                    @if($article->category)
                        <span aria-hidden="true">/</span>
                        <span x-text="previewLang === 'en' ? '{{ $article->category->getTranslation('name', 'en') }}' : '{{ $article->category->getTranslation('name', 'id') }}'"></span>
                    @endif
                </nav>

                {{-- Header --}}
                <header class="news-detail-header">
                    @if($article->category)
                        <span class="news-filter" x-text="previewLang === 'en' ? '{{ $article->category->getTranslation('name', 'en') }}' : '{{ $article->category->getTranslation('name', 'id') }}'"></span>
                    @endif
                    <h1 x-show="previewLang === 'en'">{{ $article->getTranslation('title', 'en') }}</h1>
                    <h1 x-show="previewLang === 'id'" style="display:none;">{{ $article->getTranslation('title', 'id') }}</h1>

                    <div class="news-author">
                        @if($article->author->avatar)
                            <img class="news-author-avatar" src="{{ $article->author->avatarUrl() }}" alt="" width="48" height="48">
                        @else
                            <span class="news-author-avatar" aria-hidden="true">{{ Str::upper(Str::substr($article->author->name, 0, 1)) }}</span>
                        @endif
                        <div>
                            <p>{{ $article->author->name }}</p>
                            <p class="news-published">Submitted on <time>{{ $article->created_at->format('d F Y') }}</time></p>
                        </div>
                    </div>
                </header>

                {{-- Thumbnail --}}
                @if($article->thumbnail)
                    <figure class="news-featured-image">
                        <img src="{{ $article->thumbnailUrl() }}" alt="" decoding="async">
                    </figure>
                @endif

                {{-- Content --}}
                <div x-show="previewLang === 'en'" class="article-content news-article-content">
                    {!! $article->getTranslation('content', 'en') !!}
                </div>
                <div x-show="previewLang === 'id'" class="article-content news-article-content" style="display:none;">
                    {!! $article->getTranslation('content', 'id') !!}
                </div>

                {{-- Tags --}}
                @if($article->tags->isNotEmpty())
                    <div class="news-tags news-detail-tags">
                        <span>{{ __('Tags:') }}</span>
                        @foreach($article->tags as $tag)
                            <span class="news-tag">#{{ $tag->getTranslation('name', app()->getLocale()) }}</span>
                        @endforeach
                    </div>
                @endif

            </div>
        </article>
    </div>

    @push('scripts')
    <script>
        (function () {
            var bar     = document.getElementById('preview-bar');
            var article = document.getElementById('preview-article');

            function syncHeight() {
                if (!bar || !article) return;
                var h = bar.getBoundingClientRect().height;
                document.documentElement.style.setProperty('--toolbar-h', h + 'px');
            }

            syncHeight();
            window.addEventListener('resize', syncHeight);
            // Observe toolbar size changes (reject drawer toggle)
            if (typeof ResizeObserver !== 'undefined') {
                new ResizeObserver(syncHeight).observe(bar);
            }

            // Sync Alpine.js language toggle active class (CSS-based)
            document.querySelectorAll('.pb-lang-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.pb-lang-btn').forEach(function(b) { b.classList.remove('active'); });
                    btn.classList.add('active');
                });
            });
        })();
    </script>
    @endpush

</x-layouts.public>
