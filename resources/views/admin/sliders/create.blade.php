<x-layouts.admin title="Add Slider">
    <div class="max-w-3xl mx-auto space-y-6" x-data="{
        lang: '{{ $errors->hasAny(['title_id', 'description_id']) && !$errors->hasAny(['title_en', 'description_en']) ? 'id' : 'en' }}',
        desktopPreview: null,
        mobilePreview: null
    }">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Add Slider</h2>
                <p class="text-sm text-slate-500 mt-1">Add a new hero banner with desktop and mobile image versions.</p>
            </div>
            <a href="{{ route('admin.sliders.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
                ← Back
            </a>
        </div>

        <form action="{{ route('admin.sliders.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Language Switcher --}}
            <div class="flex items-center gap-2 p-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider pl-3">Editing Language:</span>
                <button type="button" @click="lang = 'en'"
                        :class="lang === 'en' ? 'bg-brand-600 text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="relative px-3.5 py-1.5 rounded-xl text-xs transition-all flex items-center gap-1.5">
                    <span>🇬🇧 English (EN)</span>
                    @if($errors->hasAny(['title_en', 'description_en']))
                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                    @endif
                </button>
                <button type="button" @click="lang = 'id'"
                        :class="lang === 'id' ? 'bg-brand-600 text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="relative px-3.5 py-1.5 rounded-xl text-xs transition-all flex items-center gap-1.5">
                    <span>🇮🇩 Bahasa Indonesia (ID)</span>
                    @if($errors->hasAny(['title_id', 'description_id']))
                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                    @endif
                </button>
            </div>

            {{-- Content Card --}}
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">

                {{-- English Fields --}}
                <div x-show="lang === 'en'" class="space-y-4">
                    <div>
                        <label for="title_en" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Title <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="title_en" id="title_en" value="{{ old('title_en') }}"
                               placeholder="Hero title in English…"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-400 bg-slate-50/50 @error('title_en') border-rose-400 @enderror">
                        @error('title_en')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description_en" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Description
                            <span class="ml-1 text-xs font-normal text-slate-400">(optional)</span>
                        </label>
                        <textarea name="description_en" id="description_en" rows="3"
                                  placeholder="Short description in English…"
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-400 bg-slate-50/50 resize-none">{{ old('description_en') }}</textarea>
                        @error('description_en')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Indonesian Fields --}}
                <div x-show="lang === 'id'" class="space-y-4" style="display: none;">
                    <div>
                        <label for="title_id" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Judul <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="title_id" id="title_id" value="{{ old('title_id') }}"
                               placeholder="Judul slider dalam Bahasa Indonesia…"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-400 bg-slate-50/50 @error('title_id') border-rose-400 @enderror">
                        @error('title_id')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description_id" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Deskripsi
                            <span class="ml-1 text-xs font-normal text-slate-400">(opsional)</span>
                        </label>
                        <textarea name="description_id" id="description_id" rows="3"
                                  placeholder="Deskripsi singkat slider dalam Bahasa Indonesia…"
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-400 bg-slate-50/50 resize-none">{{ old('description_id') }}</textarea>
                        @error('description_id')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Divider --}}
                <div class="border-t border-slate-100 pt-5 space-y-5">

                    {{-- Desktop Image --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Desktop Image <span class="text-rose-500">*</span>
                        </label>
                        <p class="text-xs text-slate-400 mb-2">Recommended: 1920×600px (ratio 16:5). JPG, PNG, WebP format. Max 5MB.</p>

                        <div class="relative border-2 border-dashed border-slate-200 rounded-xl overflow-hidden bg-slate-50 hover:border-brand-400 transition-colors"
                             @dragover.prevent @drop.prevent="
                                const f = $event.dataTransfer.files[0];
                                if(f) { desktopPreview = URL.createObjectURL(f); $refs.desktopInput.files = $event.dataTransfer.files; }
                             ">
                            <template x-if="desktopPreview">
                                <div class="relative">
                                    <img :src="desktopPreview" class="w-full h-48 object-cover">
                                    <button type="button" @click="desktopPreview = null; $refs.desktopInput.value = ''"
                                            class="absolute top-2 right-2 bg-rose-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-rose-600">✕</button>
                                </div>
                            </template>
                            <template x-if="!desktopPreview">
                                <label for="image_desktop_input" class="flex flex-col items-center justify-center py-10 cursor-pointer">
                                    <svg class="w-10 h-10 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-sm font-semibold text-slate-500">Click or drag to upload desktop image</span>
                                    <span class="text-xs text-slate-400 mt-1">JPG, PNG, WebP • max 5MB</span>
                                </label>
                            </template>
                            <input id="image_desktop_input" type="file" name="image_desktop" x-ref="desktopInput"
                                   accept="image/*" class="sr-only"
                                   @change="desktopPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                        </div>
                        @error('image_desktop')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Mobile Image --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Mobile Image
                            <span class="ml-1 text-xs font-normal text-slate-400">(optional)</span>
                        </label>
                        <p class="text-xs text-slate-400 mb-2">Recommended: 768×500px (ratio 16:10). If empty, desktop image will be used. JPG, PNG, WebP format. Max 3MB.</p>

                        <div class="relative border-2 border-dashed border-slate-200 rounded-xl overflow-hidden bg-slate-50 hover:border-brand-400 transition-colors"
                             @dragover.prevent @drop.prevent="
                                const f = $event.dataTransfer.files[0];
                                if(f) { mobilePreview = URL.createObjectURL(f); $refs.mobileInput.files = $event.dataTransfer.files; }
                             ">
                            <template x-if="mobilePreview">
                                <div class="relative flex justify-center py-4 bg-slate-100">
                                    <img :src="mobilePreview" class="h-48 w-auto object-contain rounded-lg shadow">
                                    <button type="button" @click="mobilePreview = null; $refs.mobileInput.value = ''"
                                            class="absolute top-2 right-2 bg-rose-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-rose-600">✕</button>
                                </div>
                            </template>
                            <template x-if="!mobilePreview">
                                <label for="image_mobile_input" class="flex flex-col items-center justify-center py-8 cursor-pointer">
                                    <svg class="w-8 h-8 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-sm font-semibold text-slate-500">Click or drag to upload mobile image</span>
                                    <span class="text-xs text-slate-400 mt-1">JPG, PNG, WebP • max 3MB</span>
                                </label>
                            </template>
                            <input id="image_mobile_input" type="file" name="image_mobile" x-ref="mobileInput"
                                   accept="image/*" class="sr-only"
                                   @change="mobilePreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                        </div>
                        @error('image_mobile')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Sort Order & Status --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Display Order</label>
                            <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 bg-slate-50/50">
                            <p class="mt-1 text-xs text-slate-400">Lower number appears first.</p>
                        </div>
                        <div class="flex items-start pt-7">
                            <label class="flex items-center gap-3 cursor-pointer select-none">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" id="is_active" value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}
                                       class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                <span class="text-sm font-medium text-slate-700">Activate this slider</span>
                            </label>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Buttons --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.sliders.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm shadow-brand-600/30 transition-all">
                    Save Slider
                </button>
            </div>
        </form>
    </div>
</x-layouts.admin>
