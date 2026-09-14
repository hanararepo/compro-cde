@props([
    'label' => null,
    'name',
    'options' => [],
    'selected' => null,
    'required' => false,
    'placeholder' => 'Select an option',
    'helper' => null,
])

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-semibold text-slate-700">
            {{ $label }}
            @if($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    <select 
        name="{{ $name }}" 
        id="{{ $name }}" 
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'w-full px-3.5 py-2.5 bg-white border ' . ($errors->has($name) ? 'border-rose-300 ring-rose-200' : 'border-slate-200 focus:border-indigo-500 focus:ring-indigo-100') . ' rounded-xl shadow-xs text-sm text-slate-900 focus:outline-none focus:ring-4 transition-all']) }}
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        {{ $slot }}
    </select>

    @if($helper)
        <p class="text-xs text-slate-500">{{ $helper }}</p>
    @endif

    @error($name)
        <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
    @enderror
</div>
