<x-layouts.admin title="Coal Products">
    <div class="space-y-6" x-data="liveTable({{ Illuminate\Support\Js::from(route('admin.coal-products.index')) }}, {{ Illuminate\Support\Js::from(['search' => request('search', '')]) }})">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Coal Products</h1>
                <p class="text-sm text-slate-500 mt-1">Manage the products and specifications displayed on the public website.</p>
            </div>
            @can('coal-products.create')
                <a href="{{ route('admin.coal-products.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm">+ Add Product</a>
            @endcan
        </div>
        <form action="{{ route('admin.coal-products.index') }}" method="GET" class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs" @submit.prevent="fetchData()" role="search">
            <label for="coal-product-search" class="sr-only">Search products</label>
            <input id="coal-product-search" type="search" name="search" x-model="filters.search" value="{{ request('search') }}" maxlength="255" placeholder="Search products..."
                   class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
        </form>
        <div id="live-table-container">
            <x-data-table :headers="['Product Name', 'Parameters', 'Updated', 'Actions']" :paginator="$products">
                @forelse($products as $product)
                    <tr class="hover:bg-slate-50/80">
                        <td class="px-6 py-4 text-sm font-semibold text-slate-900">{{ $product->name }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ count($product->specifications) }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $product->updated_at->format('d M Y') }}</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('coal-products.show', ['coalProduct' => $product->slug]) }}" target="_blank" rel="noopener" class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-100">View</a>
                                @can('coal-products.edit')
                                    <a href="{{ route('admin.coal-products.edit', $product) }}" class="px-3 py-1.5 rounded-lg border border-brand-200 bg-brand-50 text-xs font-semibold text-brand-700 hover:bg-brand-100">Edit</a>
                                @endcan
                                @can('coal-products.delete')
                                    <form action="{{ route('admin.coal-products.destroy', $product) }}" method="POST" onsubmit="return confirm('Delete this coal product and all its specifications?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 rounded-lg border border-rose-200 bg-rose-50 text-xs font-semibold text-rose-600 hover:bg-rose-100">Delete</button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-12 text-center text-sm text-slate-500">No coal products found.</td></tr>
                @endforelse
            </x-data-table>
        </div>
    </div>
</x-layouts.admin>
