<x-layouts.admin title="Edit Article Category">
    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Article Category</h2>
                <p class="text-sm text-slate-500 mt-1">Update category details and multilingual names.</p>
            </div>
            <a href="{{ route('admin.article-categories.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
                ← Back to Categories
            </a>
        </div>

        <form action="{{ route('admin.article-categories.update', $articleCategory) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                <x-form.input label="Name (English)" name="name_en" :value="$articleCategory->getTranslation('name', 'en')" required />
                <x-form.input label="Name (Bahasa Indonesia)" name="name_id" :value="$articleCategory->getTranslation('name', 'id')" required />
                <x-form.input label="Slug" name="slug" :value="$articleCategory->slug" required />

                <x-form.select label="Parent Category" name="parent_id" placeholder="None (Top Level Root)">
                    @foreach($parentCategories as $parent)
                        <option value="{{ $parent->id }}" {{ old('parent_id', $articleCategory->parent_id) == $parent->id ? 'selected' : '' }}>
                            {{ $parent->getTranslation('name', 'en') }}
                        </option>
                    @endforeach
                </x-form.select>

                <div class="grid grid-cols-2 gap-4">
                    <x-form.input label="Badge Color" name="color" type="color" :value="$articleCategory->color ?? '#6366f1'" />
                    <x-form.input label="Sort Order" name="sort_order" type="number" :value="$articleCategory->sort_order" />
                </div>

                <x-form.textarea label="Description" name="description" rows="3" :value="$articleCategory->description" />
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.article-categories.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm shadow-brand-600/30 transition-all">
                    Update Category
                </button>
            </div>
        </form>
    </div>
</x-layouts.admin>
