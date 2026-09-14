<x-layouts.admin title="Global Website Settings">
    <div class="max-w-5xl mx-auto space-y-6" x-data="{ 
        activeTab: 'identity',
        selectedAccent: '{{ \App\Models\Setting::get('admin_accent_color', 'emerald') }}',
        selectedSidebar: '{{ \App\Models\Setting::get('admin_sidebar_theme', 'brand_dark') }}'
    }">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Global Website Settings</h2>
                <p class="text-sm text-slate-500 mt-1">Configure website identity, contact details, social channels, and global SEO.</p>
            </div>
        </div>

        <!-- Tab Navigation Buttons -->
        <div class="flex items-center gap-2 p-1.5 bg-slate-200/70 rounded-2xl w-fit flex-wrap">
            <button type="button" @click="activeTab = 'identity'" :class="activeTab === 'identity' ? 'bg-white text-brand-600 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium'" class="px-4 py-2 rounded-xl text-sm transition-all">
                Site Identity
            </button>
            <button type="button" @click="activeTab = 'contact'" :class="activeTab === 'contact' ? 'bg-white text-brand-600 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium'" class="px-4 py-2 rounded-xl text-sm transition-all">
                Contact & Location
            </button>
            <button type="button" @click="activeTab = 'social'" :class="activeTab === 'social' ? 'bg-white text-brand-600 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium'" class="px-4 py-2 rounded-xl text-sm transition-all">
                Social Media
            </button>
            <button type="button" @click="activeTab = 'seo'" :class="activeTab === 'seo' ? 'bg-white text-brand-600 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium'" class="px-4 py-2 rounded-xl text-sm transition-all">
                Basic SEO
            </button>
            @if(auth()->user()->can('settings.appearance') || auth()->user()->hasRole('Administrator'))
            <button type="button" @click="activeTab = 'appearance'" :class="activeTab === 'appearance' ? 'bg-white text-brand-600 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium'" class="px-4 py-2 rounded-xl text-sm transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                </svg>
                <span>Dashboard Theme</span>
            </button>
            @endif
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6"
              x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @method('PUT')

            <!-- Tab 1: Identity -->
            <div x-show="activeTab === 'identity'" class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Website Identity</h3>

                @if(isset($groupedSettings['identity']))
                    @foreach($groupedSettings['identity'] as $item)
                        @if($item['type'] === 'textarea')
                            <x-form.textarea :label="$item['label']" :name="$item['key']" :value="$item['value']" />
                        @elseif($item['type'] === 'file')
                            {{-- Dropzone file upload --}}
                            <div
                                x-data="{
                                    preview: '{{ $item['value'] ? $item['value'] : '' }}',
                                    fileName: '',
                                    dragging: false,
                                    pickFile(files) {
                                        const file = files[0];
                                        if (!file) return;
                                        this.fileName = file.name;
                                        this.preview = URL.createObjectURL(file);
                                    }
                                }"
                                @dragover.prevent="dragging = true"
                                @dragleave.prevent="dragging = false"
                                @drop.prevent="dragging = false; pickFile($event.dataTransfer.files); $refs.fileInput_{{ $item['key'] }}.files = $event.dataTransfer.files"
                            >
                                <label class="block text-sm font-medium text-slate-700 mb-2">{{ $item['label'] }}</label>

                                <div class="flex items-start gap-4">
                                    {{-- Preview box --}}
                                    <div class="shrink-0 w-24 h-24 rounded-xl border-2 border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden">
                                        <template x-if="preview">
                                            <img :src="preview" alt="Preview" class="w-full h-full object-contain p-1">
                                        </template>
                                        <template x-if="!preview">
                                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </template>
                                    </div>

                                    {{-- Dropzone --}}
                                    <div class="flex-1">
                                        <div
                                            @click="$refs.fileInput_{{ $item['key'] }}.click()"
                                            :class="dragging ? 'border-brand-400 bg-brand-50' : 'border-slate-300 bg-slate-50 hover:border-brand-400 hover:bg-brand-50/50'"
                                            class="relative border-2 border-dashed rounded-xl px-5 py-5 cursor-pointer transition-all duration-200 group"
                                        >
                                            <div class="flex flex-col items-center text-center gap-1.5">
                                                <div :class="dragging ? 'bg-brand-100 text-brand-500' : 'bg-slate-100 text-slate-400 group-hover:bg-brand-100 group-hover:text-brand-500'" class="w-10 h-10 rounded-full flex items-center justify-center transition-all">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <template x-if="!fileName">
                                                        <div>
                                                            <p class="text-sm font-medium text-slate-600 group-hover:text-brand-600 transition-colors">
                                                                <span class="text-brand-600">Klik untuk pilih</span> atau drag & drop
                                                            </p>
                                                            @if($item['key'] === 'site_favicon')
                                                                <p class="text-xs text-slate-400 mt-0.5">Rekomendasi: <strong>32×32px</strong> atau <strong>64×64px</strong> (persegi 1:1). Format ICO, PNG, SVG — maks 512 KB</p>
                                                            @elseif($item['key'] === 'site_logo')
                                                                <p class="text-xs text-slate-400 mt-0.5">Rekomendasi: <strong>240×60px</strong> s/d <strong>400×100px</strong> (rasio horizontal). Format PNG transparan, SVG, WebP — maks 2 MB</p>
                                                            @else
                                                                <p class="text-xs text-slate-400 mt-0.5">PNG, JPG, SVG — maks {{ $item['key'] === 'site_favicon' ? '512' : '2.048' }} KB</p>
                                                            @endif
                                                        </div>
                                                    </template>
                                                    <template x-if="fileName">
                                                        <div class="flex items-center gap-1.5">
                                                            <svg class="w-4 h-4 text-brand-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            <p class="text-sm font-medium text-brand-600 truncate max-w-xs" x-text="fileName"></p>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>

                                            <input
                                                x-ref="fileInput_{{ $item['key'] }}"
                                                type="file"
                                                name="{{ $item['key'] }}"
                                                id="{{ $item['key'] }}"
                                                accept="image/*"
                                                class="sr-only"
                                                @change="pickFile($event.target.files)"
                                            >
                                        </div>

                                        @if($item['value'])
                                            <p class="mt-1.5 text-xs text-slate-400">File saat ini tersimpan. Biarkan kosong jika tidak ingin mengganti.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            <x-form.input :label="$item['label']" :name="$item['key']" :value="$item['value']" />
                        @endif
                    @endforeach
                @endif
            </div>

            <!-- Tab 2: Contact & Location -->
            <div x-show="activeTab === 'contact'" class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5" style="display: none;">
                <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Contact & Location</h3>

                @if(isset($groupedSettings['contact']))
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        @foreach($groupedSettings['contact'] as $item)
                            <div class="{{ $item['type'] === 'textarea' ? 'md:col-span-2' : '' }}">
                                @if($item['type'] === 'textarea')
                                    <x-form.textarea :label="$item['label']" :name="$item['key']" :value="$item['value']" />
                                @else
                                    <x-form.input :label="$item['label']" :name="$item['key']" :type="$item['type']" :value="$item['value']" />
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Tab 3: Social Media -->
            <div x-show="activeTab === 'social'" class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5" style="display: none;">
                <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Social Media URLs</h3>

                @if(isset($groupedSettings['social']))
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        @foreach($groupedSettings['social'] as $item)
                            <x-form.input :label="$item['label']" :name="$item['key']" type="url" :value="$item['value']" placeholder="https://..." />
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Tab 4: SEO -->
            <div x-show="activeTab === 'seo'" class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5" style="display: none;">
                <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Basic Global SEO</h3>

                @if(isset($groupedSettings['seo']))
                    @foreach($groupedSettings['seo'] as $item)
                        @if($item['type'] === 'textarea')
                            <x-form.textarea :label="$item['label']" :name="$item['key']" :value="$item['value']" />
                        @elseif($item['type'] === 'file')
                            {{-- Dropzone file upload for SEO (OG Image) --}}
                            <div
                                x-data="{
                                    preview: '{{ $item['value'] ? $item['value'] : '' }}',
                                    fileName: '',
                                    dragging: false,
                                    pickFile(files) {
                                        const file = files[0];
                                        if (!file) return;
                                        this.fileName = file.name;
                                        this.preview = URL.createObjectURL(file);
                                    }
                                }"
                                @dragover.prevent="dragging = true"
                                @dragleave.prevent="dragging = false"
                                @drop.prevent="dragging = false; pickFile($event.dataTransfer.files); $refs.fileInput_{{ $item['key'] }}.files = $event.dataTransfer.files"
                            >
                                <label class="block text-sm font-medium text-slate-700 mb-2">{{ $item['label'] }}</label>

                                <div class="flex items-start gap-4">
                                    {{-- Preview box --}}
                                    <div class="shrink-0 w-32 h-20 rounded-xl border-2 border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden">
                                        <template x-if="preview">
                                            <img :src="preview" alt="Preview" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!preview">
                                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </template>
                                    </div>

                                    {{-- Dropzone --}}
                                    <div class="flex-1">
                                        <div
                                            @click="$refs.fileInput_{{ $item['key'] }}.click()"
                                            :class="dragging ? 'border-brand-400 bg-brand-50' : 'border-slate-300 bg-slate-50 hover:border-brand-400 hover:bg-brand-50/50'"
                                            class="relative border-2 border-dashed rounded-xl px-5 py-4 cursor-pointer transition-all duration-200 group"
                                        >
                                            <div class="flex flex-col items-center text-center gap-1.5">
                                                <div :class="dragging ? 'bg-brand-100 text-brand-500' : 'bg-slate-100 text-slate-400 group-hover:bg-brand-100 group-hover:text-brand-500'" class="w-9 h-9 rounded-full flex items-center justify-center transition-all">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <template x-if="!fileName">
                                                        <div>
                                                            <p class="text-sm font-medium text-slate-600 group-hover:text-brand-600 transition-colors">
                                                                <span class="text-brand-600">Klik untuk pilih</span> atau drag & drop
                                                            </p>
                                                            <p class="text-xs text-slate-400 mt-0.5">Rekomendasi: <strong>1200×630px</strong> (rasio 1.91:1 untuk social share preview). Format JPG, PNG, WebP — maks 2 MB</p>
                                                        </div>
                                                    </template>
                                                    <template x-if="fileName">
                                                        <div class="flex items-center gap-1.5">
                                                            <svg class="w-4 h-4 text-brand-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            <p class="text-sm font-medium text-brand-600 truncate max-w-xs" x-text="fileName"></p>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>

                                            <input
                                                x-ref="fileInput_{{ $item['key'] }}"
                                                type="file"
                                                name="{{ $item['key'] }}"
                                                id="{{ $item['key'] }}"
                                                accept="image/*"
                                                class="sr-only"
                                                @change="pickFile($event.target.files)"
                                            >
                                        </div>

                                        @if($item['value'])
                                            <p class="mt-1.5 text-xs text-slate-400">File saat ini tersimpan. Biarkan kosong jika tidak ingin mengganti.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            <x-form.input :label="$item['label']" :name="$item['key']" :value="$item['value']" />
                        @endif
                    @endforeach
                @endif
            </div>

            <!-- Tab 5: Dashboard Appearance & Theme (ACL Protected: settings.appearance) -->
            @if(auth()->user()->can('settings.appearance') || auth()->user()->hasRole('Administrator'))
            <div x-show="activeTab === 'appearance'" class="space-y-6" style="display: none;">
                <!-- Header Card -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-2">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Dashboard Theme & Colors</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Konfigurasi visual tema dan warna sidebar khusus untuk dashboard admin secara global.</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200/80 w-fit">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            ACL: settings.appearance
                        </span>
                    </div>

                    <!-- Global Note -->
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-600 flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-brand-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Pengaturan ini bersifat <strong>Global</strong> dan hanya berlaku pada <strong>Dashboard Admin</strong> (halaman publik website tidak terpengaruh). Ketika Anda menyimpan perubahan, semua user lain yang login ke admin akan langsung melihat tema yang baru.</span>
                    </div>
                </div>

                <!-- Accent Color Selection Card -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">1. Warna Aksen Dashboard (Accent / Brand Color)</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Pilih palet warna utama untuk tombol, status aktif, border sorotan, dan ikon di seluruh dashboard admin.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-1">
                        @foreach(\App\Services\Setting\SettingService::ACCENT_PALETTES as $key => $palette)
                            <label 
                                @click="selectedAccent = '{{ $key }}'" 
                                :class="selectedAccent === '{{ $key }}' ? 'ring-2 ring-brand-600 border-brand-600 bg-brand-50/20' : 'border-slate-200 hover:border-slate-300 bg-white'"
                                class="relative flex flex-col p-4 rounded-xl border cursor-pointer transition-all shadow-xs group select-none"
                            >
                                <input 
                                    type="radio" 
                                    name="admin_accent_color" 
                                    value="{{ $key }}" 
                                    x-model="selectedAccent" 
                                    class="sr-only"
                                >
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-6 h-6 rounded-full ring-2 ring-white shadow-sm shrink-0" style="background-color: {{ $palette['preview'] }};"></div>
                                        <span class="font-bold text-xs text-slate-800">{{ $palette['name'] }}</span>
                                    </div>
                                    <div 
                                        :class="selectedAccent === '{{ $key }}' ? 'border-brand-600 bg-brand-600' : 'border-slate-300 bg-white'" 
                                        class="w-4 h-4 rounded-full border flex items-center justify-center transition-all"
                                    >
                                        <svg x-show="selectedAccent === '{{ $key }}'" class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>
                                <!-- Shade spectrum bar -->
                                <div class="h-2 w-full rounded-full flex overflow-hidden border border-slate-200/50">
                                    <div class="flex-1" style="background-color: {{ $palette['shades'][300] }};"></div>
                                    <div class="flex-1" style="background-color: {{ $palette['shades'][500] }};"></div>
                                    <div class="flex-1" style="background-color: {{ $palette['shades'][600] }};"></div>
                                    <div class="flex-1" style="background-color: {{ $palette['shades'][800] }};"></div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Sidebar Background Selection Card -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">2. Tipe & Warna Background Sidebar</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Pilih gaya visual background untuk bilah navigasi kiri (sidebar).</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 pt-1">
                        @foreach(\App\Services\Setting\SettingService::SIDEBAR_THEMES as $key => $theme)
                            <label 
                                @click="selectedSidebar = '{{ $key }}'" 
                                :class="selectedSidebar === '{{ $key }}' ? 'ring-2 ring-brand-600 border-brand-600 bg-brand-50/20' : 'border-slate-200 hover:border-slate-300 bg-white'"
                                class="relative flex flex-col p-4 rounded-xl border cursor-pointer transition-all shadow-xs group select-none"
                            >
                                <input 
                                    type="radio" 
                                    name="admin_sidebar_theme" 
                                    value="{{ $key }}" 
                                    x-model="selectedSidebar" 
                                    class="sr-only"
                                >

                                <div class="flex items-center justify-between mb-3">
                                    <span class="font-bold text-sm text-slate-900">{{ $theme['name'] }}</span>
                                    <div 
                                        :class="selectedSidebar === '{{ $key }}' ? 'border-brand-600 bg-brand-600' : 'border-slate-300 bg-white'" 
                                        class="w-4 h-4 rounded-full border flex items-center justify-center transition-all"
                                    >
                                        <svg x-show="selectedSidebar === '{{ $key }}'" class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>

                                <!-- Miniature visual graphic of the sidebar -->
                                <div class="w-full h-24 rounded-lg overflow-hidden border border-slate-200 mb-3 flex bg-slate-100 shadow-inner">
                                    <!-- Mini Sidebar -->
                                    <div class="w-1/3 h-full p-2 flex flex-col justify-between {{ $theme['preview_bg'] }} border-r {{ $theme['preview_border'] }}">
                                        <div class="space-y-1">
                                            <div class="w-full h-2 rounded bg-brand-600/80"></div>
                                            <div class="w-3/4 h-1.5 rounded opacity-40 {{ $key === 'white' ? 'bg-slate-400' : 'bg-white' }}"></div>
                                            <div class="w-2/3 h-1.5 rounded opacity-30 {{ $key === 'white' ? 'bg-slate-400' : 'bg-white' }}"></div>
                                        </div>
                                        <div class="w-full h-1.5 rounded opacity-20 {{ $key === 'white' ? 'bg-slate-400' : 'bg-white' }}"></div>
                                    </div>
                                    <!-- Mini Content -->
                                    <div class="flex-1 p-2 space-y-1.5 bg-slate-50">
                                        <div class="w-1/2 h-2 rounded bg-slate-200"></div>
                                        <div class="w-full h-8 rounded bg-white border border-slate-200/80"></div>
                                    </div>
                                </div>

                                <p class="text-xs text-slate-500 leading-relaxed">{{ $theme['description'] }}</p>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Submit Button -->
            <div class="flex items-center justify-end">
                <button
                    type="submit"
                    :disabled="submitting"
                    :class="submitting ? 'opacity-75 cursor-not-allowed' : 'hover:bg-brand-700'"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-brand-600 text-white text-sm font-semibold shadow-sm shadow-brand-600/30 transition-all"
                >
                    <svg x-show="submitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="submitting ? 'Menyimpan...' : 'Save Global Settings'"></span>
                </button>
            </div>
        </form>
    </div>
</x-layouts.admin>
