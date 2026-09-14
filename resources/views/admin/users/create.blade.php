<x-layouts.admin title="Create User">
    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Add New User</h2>
                <p class="text-sm text-slate-500 mt-1">Create user credentials and assign access role.</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
                ← Back to Users
            </a>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                <x-form.input label="Full Name" name="name" required placeholder="John Doe" />
                <x-form.input label="Email Address" name="email" type="email" required placeholder="john@example.com" />
                <x-form.input label="Password" name="password" type="password" required placeholder="••••••••" helper="Minimum 8 characters." />

                <x-form.select label="Role" name="role" required>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </x-form.select>

                <x-form.input label="Avatar Image" name="avatar" type="file" helper="Rekomendasi: 400×400px (rasio 1:1 persegi). Format JPG, PNG, WebP, maks 2MB." />

                <div class="flex items-center gap-3 pt-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    <label for="is_active" class="text-sm font-medium text-slate-700">Account is active (can log in)</label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm shadow-brand-600/30 transition-all">
                    Create User
                </button>
            </div>
        </form>
    </div>
</x-layouts.admin>
