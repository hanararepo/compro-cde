<x-layouts.admin title="Edit Article">
    <div class="max-w-5xl mx-auto space-y-6" x-data="{ 
        lang: '{{ $errors->hasAny(['title_id','content_id','slug_id','summary_id']) ? 'id' : 'en' }}',
        showPreviewModal: false,
        titleEn: `{{ addslashes($article->getTranslation('title', 'en', false)) }}`,
        titleId: `{{ addslashes($article->getTranslation('title', 'id', false)) }}`,
        summaryEn: `{{ addslashes($article->getTranslation('summary', 'en', false) ?? '') }}`,
        summaryId: `{{ addslashes($article->getTranslation('summary', 'id', false) ?? '') }}`,
        contentEn: {{ json_encode($article->getTranslation('content', 'en', false)) }},
        contentId: {{ json_encode($article->getTranslation('content', 'id', false)) }},
        previewTab: 'en'
    }">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Article</h2>
                <p class="text-sm text-slate-500 mt-1">Status: <span class="font-semibold capitalize">{{ $article->status->label() }}</span></p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.articles.preview', $article) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-brand-200 bg-brand-50 hover:bg-brand-100 text-brand-700 text-xs font-bold transition-all shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    <span>Full Preview Mode ↗</span>
                </a>
                <a href="{{ route('admin.articles.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
                    ← Back
                </a>
            </div>
        </div>

        @if($article->rejection_reason)
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                <strong>Rejection Reason from Editor:</strong>
                <p class="mt-1">{{ $article->rejection_reason }}</p>
            </div>
        @endif

        <form action="{{ route('admin.articles.update', $article) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Language Switcher Bar -->
            <div class="flex items-center justify-between p-3 bg-white rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider pl-2">Editing Language:</span>
                    <button type="button" @click="lang = 'en'" :class="lang === 'en' ? 'bg-brand-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="relative px-3.5 py-1.5 rounded-xl text-xs transition-all">
                        🇬🇧 English (EN)
                        @if($errors->hasAny(['title_en', 'content_en', 'slug_en', 'summary_en']))
                            <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-rose-500"></span>
                        @endif
                    </button>
                    <button type="button" @click="lang = 'id'" :class="lang === 'id' ? 'bg-brand-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="relative px-3.5 py-1.5 rounded-xl text-xs transition-all">
                        🇮🇩 Bahasa Indonesia (ID)
                        @if($errors->hasAny(['title_id', 'content_id', 'slug_id', 'summary_id']))
                            <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-rose-500"></span>
                        @endif
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left 2 Cols: Main Content (Translatable) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- English Section -->
                    <div x-show="lang === 'en'" class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                        <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                            <span class="text-xs font-bold px-2 py-0.5 rounded bg-brand-50 text-brand-700">EN</span>
                            <h3 class="text-sm font-bold text-slate-900">English Content</h3>
                        </div>

                        <div class="space-y-1.5">
                            <label for="title_en" class="block text-sm font-semibold text-slate-700">Title (English) <span class="text-rose-500">*</span></label>
                            <input type="text" name="title_en" id="title_en" x-model="titleEn" required class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl shadow-xs text-sm focus:outline-none focus:ring-4 focus:ring-brand-100 focus:border-brand-500 transition-all">
                        </div>

                        <x-form.input label="Slug (English)" name="slug_en" :value="$article->getTranslation('slug', 'en', false)" />

                        <div class="space-y-1.5">
                            <label for="summary_en" class="block text-sm font-semibold text-slate-700">Summary / Excerpt (English)</label>
                            <textarea name="summary_en" id="summary_en" x-model="summaryEn" rows="3" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl shadow-xs text-sm focus:outline-none focus:ring-4 focus:ring-brand-100 focus:border-brand-500 transition-all"></textarea>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-sm font-semibold text-slate-700">Article Body (English) <span class="text-rose-500">*</span></label>
                            <textarea name="content_en" id="content_en" x-model="contentEn" class="hidden"></textarea>
                            <div class="ql-wrapper" data-placeholder="Write the full article content in English...">
                                <div id="quill-en"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Indonesian Section -->
                    <div x-show="lang === 'id'" class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5" style="display: none;">
                        <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                            <span class="text-xs font-bold px-2 py-0.5 rounded bg-brand-50 text-brand-700">ID</span>
                            <h3 class="text-sm font-bold text-slate-900">Indonesian Content (Bahasa Indonesia)</h3>
                        </div>

                        <div class="space-y-1.5">
                            <label for="title_id" class="block text-sm font-semibold text-slate-700">Judul Artikel (Indonesia) <span class="text-rose-500">*</span></label>
                            <input type="text" name="title_id" id="title_id" x-model="titleId" required class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl shadow-xs text-sm focus:outline-none focus:ring-4 focus:ring-brand-100 focus:border-brand-500 transition-all">
                        </div>

                        <x-form.input label="Slug (Indonesia)" name="slug_id" :value="$article->getTranslation('slug', 'id', false)" />

                        <div class="space-y-1.5">
                            <label for="summary_id" class="block text-sm font-semibold text-slate-700">Ringkasan Artikel (Indonesia)</label>
                            <textarea name="summary_id" id="summary_id" x-model="summaryId" rows="3" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl shadow-xs text-sm focus:outline-none focus:ring-4 focus:ring-brand-100 focus:border-brand-500 transition-all"></textarea>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-sm font-semibold text-slate-700">Konten Artikel (Indonesia) <span class="text-rose-500">*</span></label>
                            <textarea name="content_id" id="content_id" x-model="contentId" class="hidden"></textarea>
                            <div class="ql-wrapper" data-placeholder="Tuliskan konten artikel lengkap dalam Bahasa Indonesia...">
                                <div id="quill-id"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right 1 Col: Publishing Meta -->
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                        <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Publishing Options</h3>

                        <x-form.select label="Category" name="article_category_id">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('article_category_id', $article->article_category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->getTranslation('name', 'en') }} ({{ $category->getTranslation('name', 'id') }})
                                </option>
                            @endforeach
                        </x-form.select>

                        <div class="space-y-2">
                            <x-form.input label="Thumbnail Image" name="thumbnail" type="file" helper="Rekomendasi: 1280×720px (rasio 16:9). Kosongkan untuk tetap pakai gambar saat ini. Format PNG, JPG, WebP, maks 2MB." />
                            @if($article->thumbnail)
                                <img src="{{ $article->thumbnailUrl() }}" alt="" class="w-full h-32 object-cover rounded-xl mt-2 ring-1 ring-slate-200">
                            @endif
                        </div>

                        @if(auth()->user()->can('articles.approve'))
                            <x-form.select label="Status" name="status">
                                @foreach(\App\Enums\ArticleStatus::cases() as $status)
                                    <option value="{{ $status->value }}" {{ old('status', $article->status->value) === $status->value ? 'selected' : '' }}>
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </x-form.select>
                        @endif

                        {{-- Tags (tom-select: multi-select + inline create) --}}
                        <div class="space-y-1.5">
                            <label for="tags-select" class="block text-sm font-semibold text-slate-700">Tags</label>
                            <select id="tags-select" name="tags[]" multiple placeholder="Type to search or create a tag...">
                                @foreach($tags as $tag)
                                    <option value="{{ $tag->id }}"
                                        {{ in_array($tag->id, old('tags', $selectedTags)) ? 'selected' : '' }}>
                                        {{ $tag->getTranslation('name', 'en') }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-slate-400 mt-1">Select existing tags or type a new name and press Enter to create it.</p>
                        </div>

                        <div class="flex items-center gap-3 pt-2">
                            <input type="checkbox" name="is_featured" id="is_featured" value="1" {{ old('is_featured', $article->is_featured) ? 'checked' : '' }} class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                            <label for="is_featured" class="text-sm font-medium text-slate-700">Feature this article on homepage</label>
                        </div>
                    </div>

                    <!-- Submit & Preview Buttons -->
                    <div class="space-y-2">
                        <button type="button" @click="previewTab = lang; showPreviewModal = true" class="w-full py-2.5 rounded-xl border border-brand-200 bg-brand-50 hover:bg-brand-100 text-brand-700 font-bold text-sm transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <span>Live Preview Article</span>
                        </button>

                        @if(auth()->user()->can('articles.approve'))
                            <button type="submit" class="w-full py-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-sm shadow-brand-600/30 transition-all">
                                Update Article
                            </button>

                            {{-- Unpublish button: only show when the article is currently Published --}}
                            @if($article->status === \App\Enums\ArticleStatus::Published)
                                <form action="{{ route('admin.articles.unpublish', $article) }}" method="POST"
                                      onsubmit="return confirm('Unpublish this article? It will no longer be visible to the public.');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-full py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-600 font-semibold text-sm transition-all flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                        </svg>
                                        <span>Unpublish Article</span>
                                    </button>
                                </form>
                            @endif

                            {{-- Re-publish button: only show when the article is Unpublished --}}
                            @if($article->status === \App\Enums\ArticleStatus::Unpublished)
                                <form action="{{ route('admin.articles.approve', $article) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-full py-2.5 rounded-xl border border-green-300 bg-green-50 hover:bg-green-100 text-green-700 font-semibold text-sm transition-all flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        <span>Re-publish Article</span>
                                    </button>
                                </form>
                            @endif
                        @else
                            @if($article->status === \App\Enums\ArticleStatus::Draft)
                                <button type="submit" name="_action" value="request_approval" class="w-full py-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-sm shadow-brand-600/30 transition-all flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Request for Approval</span>
                                </button>
                                <button type="submit" name="_action" value="set_draft" class="w-full py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-sm transition-all">
                                    Save Draft
                                </button>
                            @elseif($article->status === \App\Enums\ArticleStatus::Rejected)
                                <button type="submit" name="_action" value="request_approval" class="w-full py-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-sm shadow-brand-600/30 transition-all flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    <span>Re-submit for Approval</span>
                                </button>
                                <button type="submit" name="_action" value="set_draft" class="w-full py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-sm transition-all">
                                    Set to Draft
                                </button>
                            @elseif($article->status === \App\Enums\ArticleStatus::Pending)
                                <button type="submit" name="_action" value="save" class="w-full py-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-sm shadow-brand-600/30 transition-all">
                                    Save Changes
                                </button>
                                <button type="submit" name="_action" value="set_draft" class="w-full py-2.5 rounded-xl border border-amber-200 bg-amber-50 hover:bg-amber-100 text-amber-800 font-semibold text-sm transition-all">
                                    Set to Draft
                                </button>
                            @else
                                <button type="submit" class="w-full py-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-sm shadow-brand-600/30 transition-all">
                                    Update Article
                                </button>
                            @endif
                        @endif

                        <a href="{{ route('admin.articles.index') }}" class="block text-center w-full py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>

        <!-- Live Preview Modal (Alpine.js) -->
        <div x-show="showPreviewModal" 
             x-transition:enter="transition ease-out duration-200" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="transition ease-in duration-150" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/80 backdrop-blur-sm p-4 sm:p-6 lg:p-10 flex items-center justify-center"
             style="display: none;">
            
            <div @click.outside="showPreviewModal = false" class="bg-white rounded-3xl max-w-4xl w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-200 flex flex-col">
                <!-- Modal Topbar -->
                <div class="sticky top-0 bg-slate-900 text-white p-4 px-6 flex items-center justify-between z-10 border-b border-slate-800 rounded-t-3xl">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-brand-500/20 text-brand-300 border border-brand-500/30">
                            👁️ REAL-TIME PREVIEW
                        </span>
                        <div class="flex items-center gap-1 bg-slate-800 p-1 rounded-xl">
                            <button type="button" @click="previewTab = 'en'" :class="previewTab === 'en' ? 'bg-brand-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-lg text-xs transition-all">
                                EN
                            </button>
                            <button type="button" @click="previewTab = 'id'" :class="previewTab === 'id' ? 'bg-brand-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-lg text-xs transition-all">
                                ID
                            </button>
                        </div>
                    </div>

                    <button type="button" @click="showPreviewModal = false" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Preview Content Body -->
                <div class="p-6 sm:p-10 bg-white">
                    <header class="space-y-4 mb-8">
                        <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight" 
                            x-text="previewTab === 'en' ? (titleEn || 'Untitled Article (English)') : (titleId || 'Judul Artikel (Indonesia)')">
                        </h1>
                        <p class="text-base text-slate-500 italic" 
                           x-text="previewTab === 'en' ? summaryEn : summaryId"
                           x-show="previewTab === 'en' ? summaryEn : summaryId">
                        </p>
                        <div class="flex items-center gap-3 py-3 border-y border-slate-100 text-xs text-slate-400">
                            <span>Author: {{ $article->author->name }}</span>
                            <span>•</span>
                            <span>{{ $article->created_at->format('M d, Y') }}</span>
                        </div>
                    </header>

                    <div class="article-content"
                         x-html="previewTab === 'en' ? contentEn : contentId">
                    </div>
                    <p x-show="previewTab === 'en' ? !contentEn : !contentId"
                       class="text-slate-400 italic text-sm">
                        Write content in the editor to see the preview here...
                    </p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    {{-- Quill Rich Text Editor --}}
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <style>
        /* ─── Quill Editor ─── */
        .ql-wrapper {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .ql-wrapper.is-focused {
            border-color: #10b981;
            box-shadow: 0 0 0 4px #d1fae5;
        }
        .ql-wrapper .ql-toolbar.ql-snow {
            font-family: var(--font-site-stack);
            border: none;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 8px 10px;
        }
        .ql-wrapper .ql-container.ql-snow {
            border: none;
            font-size: 0.9rem;
            font-family: var(--font-site-stack);
            min-height: 320px;
        }
        .ql-wrapper .ql-editor {
            min-height: 320px;
            padding: 1rem 1.25rem;
            line-height: 1.75;
            color: #334155;
        }
        .ql-wrapper .ql-editor.ql-blank::before {
            color: #94a3b8;
            font-style: normal;
        }
        .ql-snow.ql-toolbar button:hover,
        .ql-snow .ql-toolbar button:hover { background: #f0fdf4; border-radius: 4px; }
        .ql-snow.ql-toolbar button.ql-active .ql-stroke,
        .ql-snow .ql-toolbar button.ql-active .ql-stroke,
        .ql-snow.ql-toolbar button:hover .ql-stroke { stroke: #059669; }
        .ql-snow.ql-toolbar button.ql-active .ql-fill,
        .ql-snow .ql-toolbar button.ql-active .ql-fill { fill: #059669; }
        .ql-snow .ql-picker-options {
            border-radius: 0.5rem;
            border-color: #e2e8f0;
            box-shadow: 0 10px 25px -5px rgb(0 0 0 / 0.1);
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                const toolbarOptions = [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    [{ indent: '-1' }, { indent: '+1' }],
                    ['link', 'blockquote', 'code-block'],
                    [{ align: [] }],
                    ['clean'],
                ];

                function initEditor(wrapperId, textareaId) {
                    const wrapper = document.getElementById(wrapperId);
                    const textarea = document.getElementById(textareaId);
                    const outerWrapper = wrapper ? wrapper.closest('.ql-wrapper') : null;
                    if (!wrapper || !textarea || !outerWrapper) return;

                    const quill = new Quill(wrapper, {
                        theme: 'snow',
                        placeholder: outerWrapper.dataset.placeholder || 'Write here...',
                        modules: { toolbar: toolbarOptions },
                    });

                    // Load existing article content into Quill
                    if (textarea.value.trim()) {
                        quill.root.innerHTML = textarea.value;
                    }

                    // Sync Quill content → hidden textarea → Alpine x-model
                    quill.on('text-change', function () {
                        const html = quill.root.innerHTML;
                        textarea.value = (html === '<p><br></p>') ? '' : html;
                        textarea.dispatchEvent(new Event('input'));
                    });

                    // Focus ring
                    quill.on('selection-change', function (range) {
                        outerWrapper.classList.toggle('is-focused', range !== null);
                    });
                }

                initEditor('quill-en', 'content_en');
                initEditor('quill-id', 'content_id');
            }, 50);
        });
    </script>

    {{-- Tom Select: multi-select with inline tag creation --}}
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <style>
        .ts-wrapper .ts-control {
            border-radius: 0.75rem;
            border-color: #e2e8f0;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            gap: 0.375rem;
        }
        .ts-wrapper.focus .ts-control {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgb(99 102 241 / 0.1);
            outline: none;
        }
        .ts-wrapper .ts-control .item {
            background: #eef2ff;
            color: #4338ca;
            border-radius: 0.5rem;
            padding: 0.125rem 0.625rem;
            font-weight: 600;
            font-size: 0.75rem;
            border: 1px solid #c7d2fe;
        }
        .ts-dropdown {
            border-radius: 0.75rem;
            border-color: #e2e8f0;
            box-shadow: 0 10px 25px -5px rgb(0 0 0 / 0.12);
            font-size: 0.875rem;
            margin-top: 4px;
        }
        .ts-dropdown .option.selected,
        .ts-dropdown .option:hover { background: #eef2ff; color: #4338ca; }
        .ts-dropdown .create { color: #6366f1; font-weight: 600; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new TomSelect('#tags-select', {
                plugins: ['remove_button'],
                create: function (input, callback) {
                    fetch('{{ route('admin.article-tags.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ name: input }),
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.id) {
                            callback({ value: data.id, text: data.name });
                        } else {
                            callback();
                        }
                    })
                    .catch(() => callback());
                },
                createOnBlur: false,
                placeholder: 'Type to search or create a tag...',
                maxOptions: 50,
            });
        });
    </script>
    @endpush
</x-layouts.admin>
