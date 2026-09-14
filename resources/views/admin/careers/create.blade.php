<x-layouts.admin title="Create Job Posting">
    <div class="max-w-5xl mx-auto space-y-6" x-data="{
        lang: '{{ $errors->hasAny(['title_id','description_id']) ? 'id' : 'en' }}',
        titleEn: '{{ old('title_en', '') }}',
        titleId: '{{ old('title_id', '') }}',
        descEn: {{ json_encode(old('description_en', '')) }},
        descId: {{ json_encode(old('description_id', '')) }},
    }">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Create Job Posting</h2>
                <p class="text-sm text-slate-500 mt-1">Create a new job opening in English and Indonesian.</p>
            </div>
            <a href="{{ route('admin.careers.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">← Back</a>
        </div>

        <form action="{{ route('admin.careers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Language Switcher Bar --}}
            <div class="flex items-center p-3 bg-white rounded-2xl border border-slate-200/80 shadow-xs gap-2">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider pl-2">Editing Language:</span>
                <button type="button" @click="lang = 'en'"
                        :class="lang === 'en' ? 'bg-brand-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="relative px-3.5 py-1.5 rounded-xl text-xs transition-all">
                    🇬🇧 English (EN)
                    @if($errors->hasAny(['title_en','description_en']))
                        <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-rose-500"></span>
                    @endif
                </button>
                <button type="button" @click="lang = 'id'"
                        :class="lang === 'id' ? 'bg-brand-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="relative px-3.5 py-1.5 rounded-xl text-xs transition-all">
                    🇮🇩 Bahasa Indonesia (ID)
                    @if($errors->hasAny(['title_id','description_id']))
                        <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-rose-500"></span>
                    @endif
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left: Translatable Content --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- English Section --}}
                    <div x-show="lang === 'en'" class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                        <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                            <span class="text-xs font-bold px-2 py-0.5 rounded bg-brand-50 text-brand-700">EN</span>
                            <h3 class="text-sm font-bold text-slate-900">English Content</h3>
                        </div>

                        <div class="space-y-1.5">
                            <label for="title_en" class="block text-sm font-semibold text-slate-700">Job Title (English) <span class="text-rose-500">*</span></label>
                            <input type="text" name="title_en" id="title_en" x-model="titleEn"
                                   value="{{ old('title_en') }}"
                                   placeholder="e.g. Senior Frontend Developer"
                                   class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl shadow-xs text-sm focus:outline-none focus:ring-4 focus:ring-brand-100 focus:border-brand-500 transition-all @error('title_en') border-rose-400 @enderror">
                            @error('title_en')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-sm font-semibold text-slate-700">Job Description (English) <span class="text-rose-500">*</span></label>
                            <textarea name="description_en" id="description_en" x-model="descEn" class="hidden"></textarea>
                            <div class="ql-wrapper" data-placeholder="Describe the role, responsibilities, requirements…">
                                <div id="quill-en"></div>
                            </div>
                            @error('description_en')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Indonesian Section --}}
                    <div x-show="lang === 'id'" class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5" style="display:none;">
                        <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                            <span class="text-xs font-bold px-2 py-0.5 rounded bg-brand-50 text-brand-700">ID</span>
                            <h3 class="text-sm font-bold text-slate-900">Konten Bahasa Indonesia</h3>
                        </div>

                        <div class="space-y-1.5">
                            <label for="title_id" class="block text-sm font-semibold text-slate-700">Judul Pekerjaan (Indonesia) <span class="text-rose-500">*</span></label>
                            <input type="text" name="title_id" id="title_id" x-model="titleId"
                                   value="{{ old('title_id') }}"
                                   placeholder="mis. Senior Frontend Developer"
                                   class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl shadow-xs text-sm focus:outline-none focus:ring-4 focus:ring-brand-100 focus:border-brand-500 transition-all @error('title_id') border-rose-400 @enderror">
                            @error('title_id')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-sm font-semibold text-slate-700">Deskripsi Pekerjaan (Indonesia) <span class="text-rose-500">*</span></label>
                            <textarea name="description_id" id="description_id" x-model="descId" class="hidden"></textarea>
                            <div class="ql-wrapper" data-placeholder="Deskripsikan peran, tanggung jawab, dan persyaratan…">
                                <div id="quill-id"></div>
                            </div>
                            @error('description_id')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                {{-- Right: Publishing Options --}}
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                        <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Job Options</h3>

                        {{-- Job Type --}}
                        <div class="space-y-1.5">
                            <label for="type" class="block text-sm font-semibold text-slate-700">Job Type <span class="text-rose-500">*</span></label>
                            <select name="type" id="type"
                                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl shadow-xs text-sm focus:outline-none focus:ring-4 focus:ring-brand-100 focus:border-brand-500 transition-all">
                                @foreach(\App\Enums\JobType::cases() as $type)
                                    <option value="{{ $type->value }}" {{ old('type', 'full_time') === $type->value ? 'selected' : '' }}>
                                        {{ $type->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        {{-- Image --}}
                        <div class="space-y-1.5">
                            <label for="image" class="block text-sm font-semibold text-slate-700">Cover Image</label>
                            <input type="file" name="image" id="image" accept="image/*"
                                   class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl shadow-xs text-sm focus:outline-none focus:ring-4 focus:ring-brand-100 focus:border-brand-500 transition-all file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                            <p class="text-xs text-slate-400">Recommended: 1280×720px. Max 2MB.</p>
                            @error('image')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        {{-- Active Toggle --}}
                        <div class="flex items-center gap-3 pt-2">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                   {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                            <label for="is_active" class="text-sm font-medium text-slate-700">Publish this job posting</label>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="space-y-2">
                        <button type="submit" class="w-full py-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-sm shadow-brand-600/30 transition-all">
                            Create Job Posting
                        </button>
                        <a href="{{ route('admin.careers.index') }}" class="block text-center w-full py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <style>
        .ql-wrapper { border:1px solid #e2e8f0; border-radius:0.75rem; overflow:hidden; box-shadow:0 1px 2px 0 rgb(0 0 0/0.05); transition:border-color 0.15s,box-shadow 0.15s; }
        .ql-wrapper.is-focused { border-color:#10b981; box-shadow:0 0 0 4px #d1fae5; }
        .ql-wrapper .ql-toolbar.ql-snow { border:none; border-bottom:1px solid #e2e8f0; background:#f8fafc; padding:8px 10px; }
        .ql-wrapper .ql-container.ql-snow { border:none; font-size:0.9rem; min-height:300px; }
        .ql-wrapper .ql-editor { min-height:300px; padding:1rem 1.25rem; line-height:1.75; color:#334155; }
        .ql-wrapper .ql-editor.ql-blank::before { color:#94a3b8; font-style:normal; }
        .ql-snow.ql-toolbar button:hover,.ql-snow .ql-toolbar button:hover { background:#f0fdf4; border-radius:4px; }
        .ql-snow.ql-toolbar button.ql-active .ql-stroke,.ql-snow .ql-toolbar button.ql-active .ql-stroke,.ql-snow.ql-toolbar button:hover .ql-stroke { stroke:#059669; }
        .ql-snow.ql-toolbar button.ql-active .ql-fill,.ql-snow .ql-toolbar button.ql-active .ql-fill { fill:#059669; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                const toolbarOptions = [
                    [{ header: [1,2,3,false] }],
                    ['bold','italic','underline','strike'],
                    [{ list:'ordered' },{ list:'bullet' }],
                    [{ indent:'-1' },{ indent:'+1' }],
                    ['link','blockquote','code-block'],
                    [{ align:[] }],
                    ['clean'],
                ];

                function initEditor(wrapperId, textareaId) {
                    const wrapper  = document.getElementById(wrapperId);
                    const textarea = document.getElementById(textareaId);
                    const outer    = wrapper ? wrapper.closest('.ql-wrapper') : null;
                    if (!wrapper || !textarea || !outer) return;

                    const quill = new Quill(wrapper, {
                        theme: 'snow',
                        placeholder: outer.dataset.placeholder || 'Write here…',
                        modules: { toolbar: toolbarOptions },
                    });

                    if (textarea.value.trim()) quill.root.innerHTML = textarea.value;

                    quill.on('text-change', function () {
                        const html = quill.root.innerHTML;
                        textarea.value = (html === '<p><br></p>') ? '' : html;
                        textarea.dispatchEvent(new Event('input'));
                    });

                    quill.on('selection-change', function (range) {
                        outer.classList.toggle('is-focused', range !== null);
                    });
                }

                initEditor('quill-en', 'description_en');
                initEditor('quill-id', 'description_id');
            }, 50);
        });
    </script>
    @endpush
</x-layouts.admin>
