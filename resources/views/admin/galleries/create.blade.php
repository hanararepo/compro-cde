<x-layouts.admin title="Upload Gallery Image">
    <div class="max-w-3xl mx-auto space-y-6" x-data="{ lang: 'en' }">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Upload Gallery Image</h2>
                <p class="text-sm text-slate-500 mt-1">Add an image with bilingual title, description, and approval workflow.</p>
            </div>
            <a href="{{ route('admin.galleries.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
                ← Back to Gallery
            </a>
        </div>

        <form action="{{ route('admin.galleries.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Language Switcher Bar -->
            <div class="flex items-center gap-2 p-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider pl-3">Language:</span>
                <button type="button" @click="lang = 'en'" :class="lang === 'en' ? 'bg-brand-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3.5 py-1.5 rounded-xl text-xs transition-all">
                    🇬🇧 English (EN)
                </button>
                <button type="button" @click="lang = 'id'" :class="lang === 'id' ? 'bg-brand-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3.5 py-1.5 rounded-xl text-xs transition-all">
                    🇮🇩 Bahasa Indonesia (ID)
                </button>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                <!-- English Fields -->
                <div x-show="lang === 'en'" class="space-y-5">
                    <x-form.input label="Title (English)" name="title_en" placeholder="Image title in English..." required />
                    <x-form.textarea label="Description (English)" name="description_en" rows="3" placeholder="Description in English..." />
                </div>

                <!-- Indonesian Fields -->
                <div x-show="lang === 'id'" class="space-y-5" style="display: none;">
                    <x-form.input label="Judul Gambar (Indonesia)" name="title_id" placeholder="Judul gambar dalam Bahasa Indonesia..." />
                    <x-form.textarea label="Deskripsi (Indonesia)" name="description_id" rows="3" placeholder="Deskripsi dalam Bahasa Indonesia..." />
                </div>

                <!-- Media Details -->
                <div class="pt-4 border-t border-slate-100 space-y-5">
                    <x-form.input label="Image File" name="image" type="file" required helper="Rekomendasi: 1200×900px (rasio 4:3). Format JPG, PNG, WebP, maks 5MB. Thumbnail otomatis di-generate." />
                    <x-form.input label="Alt Text" name="alt_text" placeholder="Accessible description of image" />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-form.select label="Gallery Category" name="gallery_category_id">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('gallery_category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->getTranslation('name', 'en') }}
                                </option>
                            @endforeach
                        </x-form.select>

                        <x-form.input label="Sort Order" name="sort_order" type="number" value="0" />
                    </div>

                    @if(auth()->user()->can('galleries.approve'))
                        <x-form.select label="Publishing Status" name="status">
                            <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Submit for Approval</option>
                            <option value="published" {{ old('status', 'published') === 'published' ? 'selected' : '' }}>Published</option>
                        </x-form.select>
                    @endif

                    <div class="flex items-center gap-3 pt-2">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        <label for="is_active" class="text-sm font-medium text-slate-700">Display this image in public gallery (when published)</label>
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.galleries.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    Cancel
                </a>

                @if(auth()->user()->can('galleries.approve'))
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm shadow-brand-600/30 transition-all">
                        Upload Image
                    </button>
                @else
                    <button type="submit" name="_action" value="set_draft" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-sm transition-all">
                        Save as Draft
                    </button>
                    <button type="submit" name="_action" value="request_approval" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm shadow-brand-600/30 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Submit for Approval</span>
                    </button>
                @endif
            </div>
        </form>
    </div>
</x-layouts.admin>
