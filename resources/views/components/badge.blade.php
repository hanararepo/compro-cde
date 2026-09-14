@props([
    'color' => 'slate',
    'size' => 'md',
])

@php
    $colorClass = match($color) {
        'green', 'emerald', 'published'   => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'amber', 'yellow', 'pending'      => 'bg-amber-50 text-amber-700 border-amber-200',
        'rose', 'red', 'rejected'         => 'bg-rose-50 text-rose-700 border-rose-200',
        'blue', 'indigo'                  => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        'draft', 'gray'                   => 'bg-slate-100 text-slate-600 border-slate-200',
        'unpublished'                     => 'bg-slate-200 text-slate-600 border-slate-300',
        default                           => 'bg-slate-100 text-slate-700 border-slate-200',
    };

    $sizeClass = match($size) {
        'sm' => 'px-2 py-0.5 text-xs',
        'lg' => 'px-3 py-1 text-sm',
        default => 'px-2.5 py-0.5 text-xs font-semibold',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full border font-medium $colorClass $sizeClass"]) }}>
    {{ $slot }}
</span>
