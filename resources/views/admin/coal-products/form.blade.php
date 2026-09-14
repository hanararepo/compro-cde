@php
    $editing  = $coalProduct->exists;
    $formTitle = $editing ? 'Edit Coal Product' : 'Add Coal Product';
    $backRoute = auth()->user()->can('coal-products.view') ? 'admin.coal-products.index' : 'admin.dashboard';

    // Detect format: new {columns, rows} vs old [{parameter, typical, rejection}]
    $rawSpec = $coalProduct->specifications ?? [];
    if (!empty($rawSpec['columns']) && isset($rawSpec['rows'])) {
        // New format
        $initColumns = $rawSpec['columns'];
        $initRows    = $rawSpec['rows'];
    } elseif (is_array($rawSpec) && !empty($rawSpec) && isset($rawSpec[0]['parameter'])) {
        // Old format → convert to new
        $initColumns = ['Parameter', 'Typical', 'Rejection'];
        $initRows    = array_map(fn ($r) => [$r['parameter'] ?? '', $r['typical'] ?? '', $r['rejection'] ?? ''], $rawSpec);
    } else {
        $initColumns = ['Parameter', 'Typical', 'Rejection'];
        $initRows    = [['', '', '']];
    }

    // Override with old() if validation failed
    if (old('columns')) {
        $initColumns = old('columns');
        $initRows    = old('rows', $initRows);
    }
@endphp
<x-layouts.admin :title="$formTitle">
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $formTitle }}</h1>
                <p class="text-sm text-slate-500 mt-1">Manage the product name and its coal specifications.</p>
            </div>
            <a href="{{ route($backRoute) }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">&larr; Back</a>
        </div>

        <form action="{{ $editing ? route('admin.coal-products.update', $coalProduct) : route('admin.coal-products.store') }}"
              method="POST" class="space-y-6"
              x-data="coalProductForm({{ Illuminate\Support\Js::from(['columns' => $initColumns, 'rows' => $initRows]) }})">
            @csrf
            @if($editing) @method('PUT') @endif

            @if($errors->any())
                <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                    <p class="font-semibold">Please check the following fields:</p>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            {{-- Product Name --}}
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
                <x-form.input label="Product Name" name="name" :value="$coalProduct->name" required maxlength="255" placeholder="e.g. CDE 4600 GAR" />
            </div>

            {{-- Column Definitions --}}
            <section class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden" aria-labelledby="columns-heading">
                <div class="p-6 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 id="columns-heading" class="text-lg font-bold text-slate-900">Column Headers</h2>
                        <p class="text-sm text-slate-500 mt-1">Define the column names for the specification table (e.g. Parameter, Typical, Rejection — or any custom columns).</p>
                    </div>
                    <span class="text-xs text-slate-500" x-text="columns.length + ' / 10 columns'" aria-live="polite"></span>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="flex flex-wrap gap-3 items-center" id="columns-list">
                        <template x-for="(col, ci) in columns" :key="'col-' + ci">
                            <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                <input :id="'col-' + ci"
                                       :name="'columns[' + ci + ']'"
                                       x-model="columns[ci]"
                                       required maxlength="100"
                                       placeholder="Column name"
                                       :aria-label="'Column ' + (ci + 1)"
                                       class="w-32 text-sm font-medium text-slate-700 bg-transparent focus:outline-none focus:ring-2 focus:ring-brand-500 rounded-lg px-1 py-0.5">
                                <button type="button"
                                        @click="removeColumn(ci)"
                                        :disabled="columns.length <= 1"
                                        :aria-label="'Remove column ' + (ci + 1)"
                                        class="text-slate-400 hover:text-rose-500 disabled:opacity-30 disabled:cursor-not-allowed transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                        <button type="button"
                                @click="addColumn()"
                                :disabled="columns.length >= 10"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-dashed border-brand-300 bg-brand-50 text-sm font-semibold text-brand-700 hover:bg-brand-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add Column
                        </button>
                    </div>
                    <p class="mt-3 text-xs text-slate-400">
                        <strong>Tip:</strong> The first column is typically the "Parameter" name. Subsequent columns are the values (e.g., Typical, Rejection, Min, Max, Unit).
                    </p>
                </div>
            </section>

            {{-- Specification Rows --}}
            <section class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden" aria-labelledby="specifications-heading">
                <div class="p-6 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 id="specifications-heading" class="text-lg font-bold text-slate-900">Specification Rows</h2>
                        <p class="text-sm text-slate-500 mt-1">Each row is one parameter. Values must match the column order defined above.</p>
                    </div>
                    <span class="text-xs text-slate-500" x-text="rows.length + ' / 200 rows'" aria-live="polite"></span>
                </div>

                {{-- Column header labels (sticky reference) --}}
                <div class="px-4 sm:px-6 pt-4 pb-2 bg-slate-50 border-b border-slate-100">
                    <div class="flex gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                        <template x-for="(col, ci) in columns" :key="'hdr-' + ci">
                            <span class="flex-1 truncate" x-text="col || ('Column ' + (ci + 1))"></span>
                        </template>
                        <span class="w-16 shrink-0"></span>{{-- placeholder for remove btn --}}
                    </div>
                </div>

                <div class="p-4 sm:p-6 space-y-3" data-specification-rows>
                    <template x-for="(row, ri) in rows" :key="'row-' + ri">
                        <fieldset class="rounded-xl border border-slate-200 bg-slate-50/60 p-3" data-specification-row>
                            <legend class="px-2 text-xs font-semibold text-slate-400" x-text="'Row ' + (ri + 1)"></legend>
                            <div class="flex flex-wrap gap-2 items-end">
                                <template x-for="(col, ci) in columns" :key="'cell-' + ri + '-' + ci">
                                    <div class="flex-1 min-w-[100px]">
                                        <label :for="'cell-' + ri + '-' + ci"
                                               class="block text-xs font-medium text-slate-500 mb-1"
                                               x-text="col || ('Column ' + (ci + 1))">
                                        </label>
                                        <input :id="'cell-' + ri + '-' + ci"
                                               :name="'rows[' + ri + '][' + ci + ']'"
                                               x-model="rows[ri][ci]"
                                               maxlength="255"
                                               placeholder="-"
                                               class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                                    </div>
                                </template>
                                <button type="button"
                                        @click="removeRow(ri)"
                                        :disabled="rows.length === 1"
                                        :aria-label="'Remove row ' + (ri + 1)"
                                        class="px-3 py-2 text-sm font-semibold text-rose-600 rounded-xl border border-rose-200 hover:bg-rose-50 disabled:opacity-40 disabled:cursor-not-allowed shrink-0">
                                    Remove
                                </button>
                            </div>
                        </fieldset>
                    </template>

                    <button type="button"
                            @click="addRow()"
                            :disabled="rows.length >= 200"
                            data-add-parameter
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-brand-200 bg-brand-50 text-sm font-semibold text-brand-700 hover:bg-brand-100 disabled:opacity-40">
                        + Add Row
                    </button>
                </div>
            </section>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route($backRoute) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm shadow-brand-600/30">
                    {{ $editing ? 'Save Changes' : 'Create Product' }}
                </button>
            </div>
        </form>
    </div>
</x-layouts.admin>
