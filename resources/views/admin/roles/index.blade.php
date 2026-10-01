<x-layouts.admin title="Roles & ACL">
    <div x-data="liveTable('{{ route('admin.roles.index') }}', { search: '{{ request('search') }}' })" class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Roles & Permissions (ACL)</h2>
                <p class="text-sm text-slate-500 mt-1">Manage user roles and granular module permission matrix.</p>
            </div>
            <a href="{{ route('admin.roles.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm shadow-brand-600/30 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Add New Role</span>
            </a>
        </div>

        <!-- Instant Live Search Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="relative flex-1">
                    <svg class="w-4 h-4 absolute left-3.5 top-3 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input 
                        type="text" 
                        x-model="filters.search" 
                        placeholder="Type to search roles instantly..." 
                        class="w-full pl-10 pr-10 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 bg-slate-50/50"
                    >
                    <div x-show="isLoading" class="absolute right-3.5 top-3" style="display: none;">
                        <svg class="animate-spin h-4 w-4 text-brand-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
                <button 
                    type="button" 
                    x-show="hasActiveFilters()" 
                    @click="resetFilters()" 
                    class="px-3 py-2 text-xs font-semibold text-rose-600 hover:text-rose-800"
                    style="display: none;"
                >
                    Clear
                </button>
            </div>
        </div>

        <!-- Live Roles Table Container -->
        <div id="live-table-container">
            <x-data-table :headers="['Role Name', 'Users Assigned', 'Permissions Count', 'Guard', 'Actions']" :paginator="$roles">
                @forelse($roles as $role)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-6 py-4 font-bold text-slate-900 flex items-center gap-2">
                            <span>{{ $role->name }}</span>
                            @if($role->name === 'Administrator')
                                <span class="text-xs px-2 py-0.5 rounded-md bg-brand-50 text-brand-700 font-semibold border border-brand-200">Super Admin (Bypass)</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">
                            {{ $role->users_count }} users
                        </td>
                        <td class="px-6 py-4">
                            @if($role->name === 'Administrator')
                                <span class="text-xs text-brand-600 font-semibold">All Permissions (Bypass)</span>
                            @else
                                <span class="text-xs px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 font-medium">
                                    {{ $role->permissions->count() }} permissions
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs font-mono text-slate-400">
                            {{ $role->guard_name }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <a href="{{ route('admin.roles.edit', $role) }}"
                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-brand-200 bg-brand-50 hover:bg-brand-100 text-brand-700 hover:text-brand-900 text-[11px] font-semibold transition-all shadow-xs">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                    </svg>
                                    Edit Permissions
                                </a>

                                @if(! in_array($role->name, ['Administrator', 'Admin']))
                                    <x-delete-confirm
                                        :action="route('admin.roles.destroy', $role)"
                                        title="Delete this role?"
                                        :message="'The role “' . $role->name . '” will be permanently deleted. Users assigned to this role will lose its permissions.'"
                                        confirm="Yes, delete"
                                    />
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-400">
                            No roles found.
                        </td>
                    </tr>
                @endforelse
            </x-data-table>
        </div>
    </div>
</x-layouts.admin>
