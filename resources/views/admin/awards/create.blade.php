<x-layouts.admin title="Add Award / Certificate">
    <div class="max-w-5xl mx-auto space-y-6" x-data="{
        lang: '{{ $errors->hasAny(['title_id','description_id']) ? 'id' : 'en' }}',
        titleEn: '{{ old('title_en', '') }}',
        titleId: '{{ old('title_id', '') }}',
        descriptionEn: {{ json_encode(old('description_en', '')) }},
        descriptionId: {{ json_encode(old('description_id', '')) }},
        imagePreview: null,
        onImageChange(e) {
            const f = e.target.files[0];
            if (f) this.imagePreview = URL.createObjectURL(f);
        }
    }">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Add Award / Certificate</h2>
                <p class="text-sm text-slate-500 mt-1">Create a multilingual award or certificate entry.</p>
            </div>
            <a href="{{ route('admin.awards.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">← Back</a>
        </div>

        <form action="{{ route('admin.awards.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Language Switcher -->
            <div class="flex items-center justify-between p-3 bg-white rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider pl-2">Editing Language:</span>
                    <button type="button" @click="lang = 'en'" :class="lang === 'en' ? 'bg-brand-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="relative px-3.5 py-1.5 rounded-xl text-xs transition-all">
                        🇬🇧 English (EN)
                        @if($errors->hasAny(['title_en', 'description_en']))
                            <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-rose-500"></span>
                        @endif
                    </button>
                    <button type="button" @click="lang = 'id'" :class="lang === 'id' ? 'bg-brand-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="relative px-3.5 py-1.5 rounded-xl text-xs transition-all">
                        🇮🇩 Bahasa Indonesia (ID)
                        @if($errors->hasAny(['title_id', 'description_id']))
                            <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-rose-500"></span>
                        @endif
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Translatable Content -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- English Section -->
                    <div x-show="lang === 'en'" class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                        <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                            <span class="text-xs font-bold px-2 py-0.5 rounded bg-brand-50 text-brand-700">EN</span>
                            <h3 class="text-sm font-bold text-slate-900">English Content</h3>
                        </div>
                        <div class="space-y-1.5">
                            <label for="title_en" class="block text-sm font-semibold text-slate-700">Title (English) <span class="text-rose-500">*</span></label>
                            <input type="text" name="title_en" id="title_en" x-model="titleEn" placeholder="Enter title in English..." required class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl shadow-xs text-sm focus:outline-none focus:ring-4 focus:ring-brand-100 focus:border-brand-500 transition-all">
                            @error('title_en') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-sm font-semibold text-slate-700">Description (English)</label>
                            <textarea name="description_en" id="description_en" x-model="descriptionEn" class="hidden"></textarea>
                            <div class="ql-wrapper" data-placeholder="Describe this award or certificate in English...">
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
                            <label for="title_id" class="block text-sm font-semibold text-slate-700">Judul (Indonesia)</label>
                            <input type="text" name="title_id" id="title_id" x-model="titleId" placeholder="Masukkan judul dalam Bahasa Indonesia..." class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl shadow-xs text-sm focus:outline-none focus:ring-4 focus:ring-brand-100 focus:border-brand-500 transition-all">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-sm font-semibold text-slate-700">Deskripsi (Indonesia)</label>
                            <textarea name="description_id" id="description_id" x-model="descriptionId" class="hidden"></textarea>
                            <div class="ql-wrapper" data-placeholder="Deskripsikan penghargaan atau sertifikat ini dalam Bahasa Indonesia...">
                                <div id="quill-id"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Settings -->
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                        <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Settings</h3>

                        <!-- Type -->
                        <div class="space-y-1.5">
                            <label class="block text-sm font-semibold text-slate-700">Type <span class="text-rose-500">*</span></label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="relative flex flex-col items-center gap-2 p-3 rounded-xl border-2 cursor-pointer transition-all {{ old('type', 'award') === 'award' ? 'border-amber-400 bg-amber-50' : 'border-slate-200 hover:border-slate-300' }}">
                                    <input type="radio" name="type" value="award" {{ old('type', 'award') === 'award' ? 'checked' : '' }} class="sr-only">
                                    <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                    </svg>
                                    <span class="text-xs font-bold text-slate-700">Award</span>
                                </label>
                                <label class="relative flex flex-col items-center gap-2 p-3 rounded-xl border-2 cursor-pointer transition-all {{ old('type') === 'certificate' ? 'border-sky-400 bg-sky-50' : 'border-slate-200 hover:border-slate-300' }}">
                                    <input type="radio" name="type" value="certificate" {{ old('type') === 'certificate' ? 'checked' : '' }} class="sr-only">
                                    <svg class="w-7 h-7 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                    </svg>
                                    <span class="text-xs font-bold text-slate-700">Certificate</span>
                                </label>
                            </div>
                            @error('type') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Date -->
                        <div class="space-y-1.5">
                            <label for="issued_date" class="block text-sm font-semibold text-slate-700">Date</label>
                            <input type="date" name="issued_date" id="issued_date" value="{{ old('issued_date') }}" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl shadow-xs text-sm focus:outline-none focus:ring-4 focus:ring-brand-100 focus:border-brand-500 transition-all">
                            <p class="text-xs text-slate-400">Date of award / certificate</p>
                            @error('issued_date') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Image Upload -->
                        <div class="space-y-1.5">
                            <label for="image" class="block text-sm font-semibold text-slate-700">Photo / Image <span class="text-rose-500">*</span></label>
                            <!-- Preview -->
                            <div x-show="imagePreview" class="mb-2 rounded-xl overflow-hidden border border-slate-200 bg-slate-50">
                                <img :src="imagePreview" class="w-full h-40 object-cover">
                            </div>
                            <label for="image" class="flex flex-col items-center justify-center gap-2 w-full h-24 border-2 border-dashed border-slate-200 rounded-xl cursor-pointer hover:border-brand-400 hover:bg-brand-50 transition-all text-xs text-slate-400 font-medium" x-show="!imagePreview">
                                <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Click to upload photo
                            </label>
                            <input type="file" name="image" id="image" accept="image/*" class="hidden" @change="onImageChange($event)" required>
                            <p class="text-xs text-slate-400">PNG, JPG, WebP — max 5MB</p>
                            @error('image') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Sort Order -->
                        <div class="space-y-1.5">
                            <label for="sort_order" class="block text-sm font-semibold text-slate-700">Sort Order</label>
                            <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl shadow-xs text-sm focus:outline-none focus:ring-4 focus:ring-brand-100 focus:border-brand-500 transition-all">
                        </div>

                        <!-- Active Toggle -->
                        <div class="flex items-center gap-3 pt-1">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                            <label for="is_active" class="text-sm font-medium text-slate-700">Active (visible on public site)</label>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="space-y-2">
                        <button type="submit" class="w-full py-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-sm shadow-brand-600/30 transition-all">
                            Save
                        </button>
                        <a href="{{ route('admin.awards.index') }}" class="block text-center w-full py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
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
        .ql-wrapper { border: 1px solid #e2e8f0; border-radius: 0.75rem; overflow: hidden; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05); transition: border-color 0.15s, box-shadow 0.15s; }
        .ql-wrapper.is-focused { border-color: #10b981; box-shadow: 0 0 0 4px #d1fae5; }
        .ql-wrapper .ql-toolbar.ql-snow { border: none; border-bottom: 1px solid #e2e8f0; background: #f8fafc; padding: 8px 10px; }
        .ql-wrapper .ql-container.ql-snow { border: none; font-size: 0.9rem; min-height: 240px; }
        .ql-wrapper .ql-editor { min-height: 240px; padding: 1rem 1.25rem; line-height: 1.75; color: #334155; }
        .ql-wrapper .ql-editor.ql-blank::before { color: #94a3b8; font-style: normal; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                const toolbarOptions = [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['link', 'blockquote'],
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
                        placeholder: outerWrapper.dataset.placeholder || '',
                        modules: { toolbar: toolbarOptions },
                    });
                    if (textarea.value.trim()) quill.root.innerHTML = textarea.value;
                    quill.on('text-change', function () {
                        const html = quill.root.innerHTML;
                        textarea.value = (html === '<p><br></p>') ? '' : html;
                        textarea.dispatchEvent(new Event('input'));
                    });
                    quill.on('selection-change', function (range) {
                        outerWrapper.classList.toggle('is-focused', range !== null);
                    });
                }
                initEditor('quill-en', 'description_en');
                initEditor('quill-id', 'description_id');
            }, 50);

            // Type radio visual update
            document.querySelectorAll('input[name="type"]').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    document.querySelectorAll('input[name="type"]').forEach(function(r) {
                        const label = r.closest('label');
                        if (r.value === 'award') {
                            label.classList.toggle('border-amber-400', r.checked);
                            label.classList.toggle('bg-amber-50', r.checked);
                            label.classList.toggle('border-slate-200', !r.checked);
                        } else {
                            label.classList.toggle('border-sky-400', r.checked);
                            label.classList.toggle('bg-sky-50', r.checked);
                            label.classList.toggle('border-slate-200', !r.checked);
                        }
                    });
                });
            });
        });
    </script>
    @endpush
</x-layouts.admin>
