<x-layouts.admin title="Edit Article Tag">
    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Article Tag</h2>
                <p class="text-sm text-slate-500 mt-1">Update tag names and slug.</p>
            </div>
            <a href="{{ route('admin.article-tags.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
                ← Back to Tags
            </a>
        </div>

        <form action="{{ route('admin.article-tags.update', $articleTag) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                <x-form.input label="Name (English)" name="name_en" :value="$articleTag->getTranslation('name', 'en')" required />
                <x-form.input label="Name (Bahasa Indonesia)" name="name_id" :value="$articleTag->getTranslation('name', 'id')" required />
                <x-form.input label="Slug" name="slug" :value="$articleTag->slug" required />

                @if($articleTag->articles_count > 0)
                    <div class="flex items-center gap-2 p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-700">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>This tag is used in <strong>{{ $articleTag->articles_count }} articles</strong>. Renaming will update it everywhere.</span>
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.article-tags.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm shadow-brand-600/30 transition-all">
                    Update Tag
                </button>
            </div>
        </form>
    </div>
</x-layouts.admin>
