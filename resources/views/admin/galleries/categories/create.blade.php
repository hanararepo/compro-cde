<x-layouts.admin title="Create Gallery Category">
    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">New Gallery Category</h2>
                <p class="text-sm text-slate-500 mt-1">Add a category for organizing photos and media.</p>
            </div>
            <a href="{{ route('admin.gallery-categories.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
                ← Back to Categories
            </a>
        </div>

        <form action="{{ route('admin.gallery-categories.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                <x-form.input label="Name (English)" name="name_en" required placeholder="Events & Activities" />
                <x-form.input label="Name (Bahasa Indonesia)" name="name_id" required placeholder="Kegiatan & Acara" />
                <x-form.input label="Slug (optional)" name="slug" placeholder="events-and-activities" helper="Auto-generated if left blank." />
                <x-form.input label="Sort Order" name="sort_order" type="number" value="0" />
                <x-form.textarea label="Description" name="description" rows="3" placeholder="Category description..." />
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.gallery-categories.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm shadow-brand-600/30 transition-all">
                    Create Category
                </button>
            </div>
        </form>
    </div>
</x-layouts.admin>
