@props(['name'])
<svg {{ $attributes->merge(['class' => 'ca-icon']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('users') <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m20 0v-2a4 4 0 0 0-3-3.87M16 3a4 4 0 0 1 0 8"/><circle cx="9" cy="7" r="4"/> @break
        @case('mail') <rect x="3" y="5" width="18" height="14" rx="3"/><path d="m3 6 9 7 9-7"/> @break
        @case('check') <circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/> @break
        @case('alert') <path d="m10.3 3.9-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.7-3.1l-8-14a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4m0 4h.01"/> @break
        @case('clock') <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/> @break
        @case('trash') <path d="M3 6h18M9 6V4h6v2M5 6l1 14h12l1-14M10 10v6m4-6v6"/> @break
        @case('search') <circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/> @break
        @case('arrow') <path d="m10 5-7 7 7 7M3 12h18"/> @break
        @case('close') <path d="m6 6 12 12M6 18 18 6"/> @break
        @case('eye') <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/> @break
        @case('file') <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6Zm0 0v6h6M8 13h8m-8 4h5"/> @break
        @case('download') <path d="M12 3v12m-4-4 4 4 4-4M4 16v5h16v-5"/> @break
        @case('briefcase') <rect x="3" y="7" width="18" height="14" rx="3"/><path d="M8 7V3h8v4M3 12l9 4 9-4m-9 0v4"/> @break
    @endswitch
</svg>
