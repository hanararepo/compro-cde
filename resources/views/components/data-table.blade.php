@props([
    'headers' => [],
    'paginator' => null,
    'emptyMessage' => 'No records found.',
])

<div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-200/80 bg-slate-50/50">
                    @foreach($headers as $header)
                        <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            {{ $header }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if($paginator && $paginator->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $paginator->links() }}
        </div>
    @endif
</div>
