<x-layouts.admin title="Add Gallery Video">
    <div class="max-w-3xl mx-auto space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Tambah Video Galeri</h2>
                <p class="text-sm text-slate-500 mt-1">Tambahkan video YouTube baru ke galeri dengan judul bilingual (EN & ID).</p>
            </div>
            <a href="{{ route('admin.gallery-videos.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
                ← Kembali
            </a>
        </div>

        <form action="{{ route('admin.gallery-videos.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Title Card --}}
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wide">Judul / Title</h3>

                {{-- English Title --}}
                <div>
                    <label for="title_en" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="text-[11px] font-bold px-1.5 py-0.5 rounded bg-brand-50 text-brand-700">EN</span>
                            Title <span class="text-rose-500">*</span>
                        </span>
                    </label>
                    <input type="text" name="title_en" id="title_en"
                           value="{{ old('title_en') }}"
                           placeholder="Video title in English…"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-400 bg-slate-50/50 @error('title_en') border-rose-400 bg-rose-50 @enderror">
                    @error('title_en')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Indonesian Title --}}
                <div>
                    <label for="title_id" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="text-[11px] font-bold px-1.5 py-0.5 rounded bg-amber-50 text-amber-700">ID</span>
                            Judul <span class="text-rose-500">*</span>
                        </span>
                    </label>
                    <input type="text" name="title_id" id="title_id"
                           value="{{ old('title_id') }}"
                           placeholder="Judul video dalam Bahasa Indonesia…"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-400 bg-slate-50/50 @error('title_id') border-rose-400 bg-rose-50 @enderror">
                    @error('title_id')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- YouTube URL Card (Alpine only for live preview) --}}
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5"
                 x-data="{
                     youtubeUrl: @js(old('youtube_url', '')),
                     videoId: null,
                     get thumbnailUrl() {
                         return this.videoId ? 'https://img.youtube.com/vi/' + this.videoId + '/hqdefault.jpg' : null;
                     },
                     extractId(url) {
                         if (!url) return null;
                         var m = url.match(/[?&]v=([a-zA-Z0-9_-]{11})/);
                         if (m) return m[1];
                         m = url.match(/youtu\.be\/([a-zA-Z0-9_-]{11})/);
                         if (m) return m[1];
                         m = url.match(/embed\/([a-zA-Z0-9_-]{11})/);
                         if (m) return m[1];
                         m = url.match(/shorts\/([a-zA-Z0-9_-]{11})/);
                         if (m) return m[1];
                         if (/^[a-zA-Z0-9_-]{11}$/.test(url.trim())) return url.trim();
                         return null;
                     },
                     updatePreview() {
                         this.videoId = this.extractId(this.youtubeUrl);
                     }
                 }"
                 x-init="updatePreview()">

                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wide">Video YouTube</h3>

                {{-- YouTube URL --}}
                <div>
                    <label for="youtube_url" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        URL atau Embed Code YouTube <span class="text-rose-500">*</span>
                    </label>
                    <p class="text-xs text-slate-400 mb-2">
                        Bisa berupa URL penuh, link <code class="bg-slate-100 px-1 rounded">youtu.be</code>, URL embed, kode iframe, atau ID video YouTube (11 karakter).
                    </p>
                    <div class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 focus-within:ring-2 focus-within:ring-brand-500 focus-within:border-brand-400 @error('youtube_url') border-rose-400 bg-rose-50 @enderror">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                        </svg>
                        <input type="text" name="youtube_url" id="youtube_url"
                               x-model="youtubeUrl"
                               @input.debounce.600ms="updatePreview()"
                               @paste="$nextTick(() => updatePreview())"
                               placeholder="https://www.youtube.com/watch?v=…"
                               class="flex-1 bg-transparent text-sm focus:outline-none font-mono min-w-0">
                    </div>
                    @error('youtube_url')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Live Video Preview --}}
                <div x-show="videoId" style="display: none;">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Preview Video</label>
                    <div class="flex items-start gap-4 p-4 bg-slate-50 rounded-xl border border-slate-200">
                        <div class="relative shrink-0 w-40 h-24 rounded-lg overflow-hidden bg-slate-900 shadow-sm">
                            <img :src="thumbnailUrl" alt="thumbnail" class="w-full h-full object-cover">
                            <div class="absolute inset-0 flex items-center justify-center bg-black/20">
                                <span class="w-8 h-8 rounded-full bg-rose-600 text-white flex items-center justify-center shadow-md">
                                    <svg class="w-4 h-4 fill-current ml-0.5" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                </span>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-slate-500 mb-1">Video ID terdeteksi</p>
                            <code class="text-sm font-mono text-slate-800 bg-white px-2 py-1 rounded-lg border border-slate-200" x-text="videoId"></code>
                            <p class="text-xs text-slate-400 mt-2">
                                <a :href="'https://youtu.be/' + videoId" target="_blank" class="text-rose-600 hover:underline">Buka di YouTube ↗</a>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Invalid URL notice --}}
                <div x-show="youtubeUrl && !videoId" style="display: none;">
                    <div class="flex items-center gap-2 p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-700">
                        <svg class="w-4 h-4 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>URL YouTube tidak valid — tidak dapat mendeteksi ID video.</span>
                    </div>
                </div>

            </div>

            {{-- Settings Card --}}
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wide mb-4">Pengaturan</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="sort_order" class="block text-sm font-semibold text-slate-700 mb-1.5">Urutan Tampil</label>
                        <input type="number" name="sort_order" id="sort_order"
                               value="{{ old('sort_order', 0) }}" min="0"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 bg-slate-50/50">
                        <p class="mt-1 text-xs text-slate-400">Angka lebih kecil tampil lebih awal.</p>
                    </div>
                    <div class="flex items-start pt-7">
                        <label class="flex items-center gap-3 cursor-pointer select-none">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                            <span class="text-sm font-medium text-slate-700">Aktifkan video ini</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Buttons --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.gallery-videos.index') }}"
                   class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm shadow-brand-600/30 transition-all">
                    Simpan Video
                </button>
            </div>
        </form>
    </div>
</x-layouts.admin>
