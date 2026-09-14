<x-layouts.admin title="Edit Gallery Image">
    <div class="max-w-3xl mx-auto space-y-6" x-data="{ lang: 'en' }">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Gallery Image</h2>
                <p class="text-sm text-slate-500 mt-1">Status: <span class="font-semibold">{{ $gallery->status->label() }}</span></p>
            </div>
            <a href="{{ route('admin.galleries.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
                ← Back to Gallery
            </a>
        </div>

        @if($gallery->rejection_reason && $gallery->status === \App\Enums\GalleryStatus::Rejected)
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                <strong>Rejection Reason from Editor:</strong>
                <p class="mt-1">{{ $gallery->rejection_reason }}</p>
            </div>
        @endif

        <form action="{{ route('admin.galleries.update', $gallery) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

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
                    <x-form.input label="Title (English)" name="title_en" :value="$gallery->getTranslation('title', 'en')" required />
                    <x-form.textarea label="Description (English)" name="description_en" rows="3" :value="$gallery->getTranslation('description', 'en')" />
                </div>

                <!-- Indonesian Fields -->
                <div x-show="lang === 'id'" class="space-y-5" style="display: none;">
                    <x-form.input label="Judul Gambar (Indonesia)" name="title_id" :value="$gallery->getTranslation('title', 'id')" required />
                    <x-form.textarea label="Deskripsi (Indonesia)" name="description_id" rows="3" :value="$gallery->getTranslation('description', 'id')" />
                </div>

                <!-- Media Details -->
                <div class="pt-4 border-t border-slate-100 space-y-5">
                    <div class="flex items-center gap-4">
                        <img src="{{ $gallery->thumbnailUrl() }}" alt="" class="w-24 h-20 object-cover rounded-xl ring-1 ring-slate-200">
                        <div class="flex-1">
                            <x-form.input label="Replace Image (optional)" name="image" type="file" helper="Rekomendasi: 1200×900px (rasio 4:3). Kosongkan untuk tetap pakai gambar saat ini. Format JPG, PNG, WebP, maks 5MB." />
                        </div>
                    </div>

                    <x-form.input label="Alt Text" name="alt_text" :value="$gallery->alt_text" />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-form.select label="Gallery Category" name="gallery_category_id">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('gallery_category_id', $gallery->gallery_category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->getTranslation('name', 'en') }}
                                </option>
                            @endforeach
                        </x-form.select>

                        <x-form.input label="Sort Order" name="sort_order" type="number" :value="$gallery->sort_order" />
                    </div>

                    @if(auth()->user()->can('galleries.approve'))
                        <x-form.select label="Publishing Status" name="status">
                            @foreach(\App\Enums\GalleryStatus::cases() as $status)
                                <option value="{{ $status->value }}" {{ old('status', $gallery->status->value) === $status->value ? 'selected' : '' }}>
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </x-form.select>
                    @endif

                    <div class="flex items-center gap-3 pt-2">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $gallery->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        <label for="is_active" class="text-sm font-medium text-slate-700">Display this image in public gallery (when published)</label>
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end gap-3 flex-wrap">
                <a href="{{ route('admin.galleries.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    Cancel
                </a>

                @if(auth()->user()->can('galleries.approve'))
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm shadow-brand-600/30 transition-all">
                        Update Gallery Item
                    </button>
                @else
                    @if($gallery->status === \App\Enums\GalleryStatus::Draft)
                        <button type="submit" name="_action" value="set_draft" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-sm transition-all">
                            Save Draft
                        </button>
                        <button type="submit" name="_action" value="request_approval" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-sm shadow-brand-600/30 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Request for Approval</span>
                        </button>
                    @elseif($gallery->status === \App\Enums\GalleryStatus::Rejected)
                        <button type="submit" name="_action" value="set_draft" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-sm transition-all">
                            Set to Draft
                        </button>
                        <button type="submit" name="_action" value="request_approval" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-sm shadow-brand-600/30 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <span>Re-submit for Approval</span>
                        </button>
                    @elseif($gallery->status === \App\Enums\GalleryStatus::Pending)
                        <button type="submit" name="_action" value="set_draft" class="px-5 py-2.5 rounded-xl border border-amber-200 bg-amber-50 hover:bg-amber-100 text-amber-800 font-semibold text-sm transition-all">
                            Set to Draft
                        </button>
                        <button type="submit" name="_action" value="save" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-sm shadow-brand-600/30 transition-all">
                            Save Changes
                        </button>
                    @else
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-sm shadow-brand-600/30 transition-all">
                            Update Gallery Item
                        </button>
                    @endif
                @endif
            </div>
        </form>
    </div>
</x-layouts.admin>
