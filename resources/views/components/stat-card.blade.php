@props([
    'title',
    'value',
    'icon' => null,
    'color' => 'indigo',
    'trend' => null,
    'trendLabel' => null,
])

@php
    $colorClasses = match($color) {
        'emerald' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
        'amber' => 'bg-amber-50 text-amber-600 border-amber-100',
        'rose' => 'bg-rose-50 text-rose-600 border-rose-100',
        'blue' => 'bg-blue-50 text-blue-600 border-blue-100',
        'purple' => 'bg-purple-50 text-purple-600 border-purple-100',
        'violet' => 'bg-violet-50 text-violet-600 border-violet-100',
        default => 'bg-indigo-50 text-indigo-600 border-indigo-100',
    };
@endphp

<div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $title }}</p>
            <p class="text-3xl font-extrabold text-slate-900 mt-2 tracking-tight">{{ $value }}</p>
            @if($trend)
                <p class="text-xs text-emerald-600 font-semibold mt-2 flex items-center gap-1">
                    <span>↑ {{ $trend }}</span>
                    @if($trendLabel)
                        <span class="text-slate-400 font-normal">{{ $trendLabel }}</span>
                    @endif
                </p>
            @endif
        </div>
        @if($icon)
            <div class="w-12 h-12 rounded-xl flex items-center justify-center border {{ $colorClasses }}">
                {{ $icon }}
            </div>
        @endif
    </div>
</div>
