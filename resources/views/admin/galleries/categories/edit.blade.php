<x-layouts.admin title="Edit Gallery Category">
    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Gallery Category</h2>
                <p class="text-sm text-slate-500 mt-1">Update category details and multilingual names.</p>
            </div>
            <a href="{{ route('admin.gallery-categories.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
                ← Back to Categories
            </a>
        </div>

        <form action="{{ route('admin.gallery-categories.update', $galleryCategory) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                <x-form.input label="Name (English)" name="name_en" :value="$galleryCategory->getTranslation('name', 'en')" required />
                <x-form.input label="Name (Bahasa Indonesia)" name="name_id" :value="$galleryCategory->getTranslation('name', 'id')" required />
                <x-form.input label="Slug" name="slug" :value="$galleryCategory->slug" required />
                <x-form.input label="Sort Order" name="sort_order" type="number" :value="$galleryCategory->sort_order" />
                <x-form.textarea label="Description" name="description" rows="3" :value="$galleryCategory->description" />
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.gallery-categories.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm shadow-brand-600/30 transition-all">
                    Update Category
                </button>
            </div>
        </form>
    </div>
</x-layouts.admin>
