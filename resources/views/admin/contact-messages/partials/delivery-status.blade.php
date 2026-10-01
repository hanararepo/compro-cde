@php
    [$deliveryLabel, $deliveryColor] = match ($contactMessage->email_status) {
        'sent' => ['Sent', 'bg-emerald-50 text-emerald-700'],
        'failed' => ['Failed', 'bg-rose-50 text-rose-700'],
        'pending' => ['Pending', 'bg-amber-50 text-amber-700'],
        default => ['Not sent', 'bg-slate-100 text-slate-500'],
    };
@endphp
<span class="inline-flex rounded-md px-2.5 py-1 text-xs font-semibold {{ $deliveryColor }}">{{ $deliveryLabel }}</span>
