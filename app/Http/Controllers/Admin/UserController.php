<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLog\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLog
    ) {}

    /**
     * Display a listing of the users with eager loaded roles, search and filters.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::with('roles')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->role, function ($query, $role) {
                $query->whereHas('roles', function ($q) use ($role) {
                    $q->where('name', $role);
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_active', $request->boolean('status'));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $this->authorize('create', User::class);

        $roles = Role::when(! auth()->user()->hasRole('Administrator'), function ($query) {
            $query->where('name', '!=', 'Administrator');
        })->orderBy('name')->get();

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $allowedRoles = Role::when(! $request->user()->hasRole('Administrator'), function ($query) {
            $query->where('name', '!=', 'Administrator');
        })->pluck('name')->toArray();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
            'role' => ['required', 'string', Rule::in($allowedRoles)],
            'is_active' => ['boolean'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ], [
            'role.in' => 'Only Administrators can assign the Administrator role.',
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'avatar' => $avatarPath,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $user->assignRole($validated['role']);

        $this->activityLog->log(auth()->user(), 'created', "Created user '{$user->name}' ({$user->email}).", $user);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        $roles = Role::when(! auth()->user()->hasRole('Administrator'), function ($query) {
            $query->where('name', '!=', 'Administrator');
        })->orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $allowedRoles = Role::when(! $request->user()->hasRole('Administrator'), function ($query) {
            $query->where('name', '!=', 'Administrator');
        })->pluck('name')->toArray();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', Password::defaults()],
            'role' => ['required', 'string', Rule::in($allowedRoles)],
            'is_active' => ['boolean'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ], [
            'role.in' => 'Only Administrators can assign the Administrator role.',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => $request->boolean('is_active', true),
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        if ($request->hasFile('avatar')) {
            $updateData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($updateData);
        $user->syncRoles([$validated['role']]);

        $this->activityLog->log(auth()->user(), 'updated', "Updated user '{$user->name}' ({$user->email}).", $user);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        $email = $user->email;
        $user->delete();

        $this->activityLog->log(auth()->user(), 'deleted', "Deleted user '{$name}' ({$email}).");

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
