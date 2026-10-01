@props([
    'action',
    'title'   => 'Delete this item?',
    'message' => 'This item will be permanently deleted.',
    'warning' => 'This action cannot be undone.',
    'confirm' => 'Yes, delete',
    'label'   => 'Delete',
    'size'    => 'sm',
    'icon'    => true,
    'triggerClass' => '',
    'method'  => 'DELETE',
])

@php
    $sizeClass = $size === 'md'
        ? 'inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 transition-colors'
        : 'inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-600 hover:text-rose-800 text-[11px] font-semibold transition-all shadow-xs';
@endphp

<div x-data="{
    open: false,
    deleting: false,
    openModal() { this.open = true; this.$nextTick(() => this.$refs.dlg.showModal()); },
    closeModal() { this.$refs.dlg.close(); this.open = false; },
    submitForm() { this.deleting = true; this.$refs.frm.submit(); }
}" class="inline-flex">

    <button type="button"
            @click="openModal()"
            class="{{ $sizeClass }} {{ $triggerClass }}">
        @if($icon)
            <svg class="{{ $size === 'md' ? 'w-4 h-4' : 'w-3 h-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
        @endif
        {{ $label }}
    </button>

    <dialog x-ref="dlg"
            class="dc-dialog"
            @cancel.prevent="closeModal()"
            @click="if ($event.target === $el) closeModal()">
        <form x-ref="frm" method="POST" action="{{ $action }}" class="dc-dialog-inner">
            @csrf
            @method($method)

            <div class="dc-dialog-mark">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>

            <h2 class="dc-dialog-title">{{ $title }}</h2>
            <p class="dc-dialog-copy">{{ $message }}</p>
            @if($warning)
                <div class="dc-dialog-warning">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    {{ $warning }}
                </div>
            @endif

            {{ $slot }}

            <div class="dc-dialog-footer">
                <button type="button"
                        class="dc-btn"
                        :disabled="deleting"
                        @click="closeModal()">Cancel</button>
                <button type="button"
                        class="dc-btn dc-btn--danger"
                        :disabled="deleting"
                        @click="submitForm()">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <span x-text="deleting ? 'Deleting\u2026' : '{{ $confirm }}'"></span>
                </button>
            </div>
        </form>
    </dialog>
</div>
