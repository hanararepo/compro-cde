<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Acl\RoleService;
use App\Services\ActivityLog\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(
        private readonly RoleService $roleService,
        private readonly ActivityLogService $activityLog
    ) {}

    /**
     * Display a listing of roles with their permission counts, search, and eager loading.
     */
    public function index(Request $request): View
    {
        $roles = Role::with('permissions')
            ->withCount('users')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role with the permission matrix.
     */
    public function create(): View
    {
        $groupedPermissions = $this->roleService->getPermissionsGroupedByModule();

        return view('admin.roles.create', compact('groupedPermissions'));
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = $this->roleService->createRole($validated);

        $this->activityLog->log(auth()->user(), 'created', "Created role '{$role->name}'.", $role);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Show the form for editing the specified role with current permissions checked.
     */
    public function edit(Role $role): View
    {
        $groupedPermissions = $this->roleService->getPermissionsGroupedByModule();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('admin.roles.edit', compact('role', 'groupedPermissions', 'rolePermissions'));
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        if (in_array($role->name, ['Administrator', 'Admin']) && $request->input('name') !== $role->name) {
            return back()->with('error', "The {$role->name} role name cannot be modified.");
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.$role->id],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $this->roleService->updateRole($role, $validated);

        $this->activityLog->log(auth()->user(), 'updated', "Updated role '{$role->name}'.", $role);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role): RedirectResponse
    {
        if (in_array($role->name, ['Administrator', 'Admin'])) {
            return back()->with('error', "Cannot delete the core {$role->name} role.");
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', 'Cannot delete role because it is currently assigned to users.');
        }

        $name = $role->name;
        $role->delete();

        $this->activityLog->log(auth()->user(), 'deleted', "Deleted role '{$name}'.");

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
