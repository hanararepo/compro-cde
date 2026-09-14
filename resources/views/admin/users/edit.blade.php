<x-layouts.admin title="Edit User: {{ $user->name }}">
    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Edit User: {{ $user->name }}</h2>
                <p class="text-sm text-slate-500 mt-1">Update profile information, role assignment, and login status.</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
                ← Back to Users
            </a>
        </div>

        <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                <x-form.input label="Full Name" name="name" :value="$user->name" required />
                <x-form.input label="Email Address" name="email" type="email" :value="$user->email" required />
                <x-form.input label="New Password (optional)" name="password" type="password" placeholder="Leave blank to keep current password" helper="Only fill if you wish to reset this user's password." />

                <x-form.select label="Role" name="role" required>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </x-form.select>

                <div class="space-y-2">
                    <x-form.input label="Avatar Image" name="avatar" type="file" helper="Rekomendasi: 400×400px (rasio 1:1 persegi). Kosongkan jika tidak ingin mengubah. Format JPG, PNG, WebP, maks 2MB." />
                    @if($user->avatar)
                        <div class="flex items-center gap-3 pt-2">
                            <img src="{{ $user->avatarUrl() }}" alt="Current Avatar" class="w-12 h-12 rounded-full object-cover ring-2 ring-slate-100">
                            <span class="text-xs text-slate-500">Current avatar</span>
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    <label for="is_active" class="text-sm font-medium text-slate-700">Account is active (can log in)</label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm shadow-brand-600/30 transition-all">
                    Update User
                </button>
            </div>
        </form>
    </div>
</x-layouts.admin>
