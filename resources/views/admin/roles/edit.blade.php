<x-layouts.admin title="Edit Role: {{ $role->name }}">
    <div class="max-w-5xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Role: {{ $role->name }}</h2>
                <p class="text-sm text-slate-500 mt-1">Modify permissions matrix for this role.</p>
            </div>
            <a href="{{ route('admin.roles.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
                ← Back to Roles
            </a>
        </div>

        <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Role Name Card -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
                <x-form.input 
                    label="Role Name" 
                    name="name" 
                    :value="$role->name"
                    :disabled="$role->name === 'Administrator'"
                    required 
                    helper="{{ $role->name === 'Administrator' ? 'The Administrator role name is locked.' : 'Update the human-readable role name.' }}"
                />
            </div>

            <!-- Permission Matrix Card -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-6">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Module Permissions Matrix</h3>
                    <p class="text-xs text-slate-500 mt-1">Select the exact permissions granted to this role across CMS modules.</p>
                </div>

                @if($role->name === 'Administrator')
                    <div class="p-4 rounded-xl bg-brand-50 border border-brand-100 text-brand-800 text-sm">
                        <strong>Note:</strong> The Administrator role has full super-admin access bypassed automatically via Gate hooks. Individual permission checkboxes do not limit Administrator access.
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($groupedPermissions as $module => $permissions)
                        <div x-data="{ 
                            allChecked: false,
                            checkAll() {
                                let checkboxes = $el.querySelectorAll('input[type=checkbox]');
                                checkboxes.forEach(cb => cb.checked = this.allChecked);
                            }
                        }" class="p-5 rounded-xl bg-slate-50/70 border border-slate-200/80 space-y-4">
                            
                            <!-- Module Header with Select All toggle -->
                            <div class="flex items-center justify-between pb-3 border-b border-slate-200/60">
                                <span class="font-bold text-sm text-slate-800 uppercase tracking-wider">
                                    {{ str_replace('-', ' ', $module) }}
                                </span>
                                <label class="inline-flex items-center gap-1.5 text-xs text-brand-600 font-semibold cursor-pointer select-none">
                                    <input type="checkbox" x-model="allChecked" @change="checkAll()" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                    <span>Select All</span>
                                </label>
                            </div>

                            <!-- Checkbox options for module -->
                            <div class="grid grid-cols-2 gap-3">
                                @foreach($permissions as $permission)
                                    @php
                                        $action = explode('.', $permission->name)[1] ?? $permission->name;
                                        $checked = in_array($permission->name, $rolePermissions);
                                    @endphp
                                    <label class="flex items-center gap-2.5 text-xs font-medium text-slate-700 cursor-pointer select-none">
                                        <input 
                                            type="checkbox" 
                                            name="permissions[]" 
                                            value="{{ $permission->name }}" 
                                            {{ $checked ? 'checked' : '' }}
                                            class="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                                        >
                                        <span class="capitalize">{{ $action }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.roles.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm shadow-brand-600/30 transition-all">
                    Update Role
                </button>
            </div>
        </form>
    </div>
</x-layouts.admin>
