@extends('admin.layout')
@section('title', $user->exists ? 'Edit user' : 'New user')

@section('content')
<div x-data="{ loading: false, show: false, name: @js(old('name', $user->name ?? '')), email: @js(old('email', $user->email ?? '')), role: @js(old('role', $user->role ?? 'editor')) }">
    {{-- Header --}}
    <div class="flex items-center gap-3 mb-5">
        <a href="/admin/users" class="w-9 h-9 rounded-lg bg-zinc-900 border border-zinc-800 hover:bg-zinc-800 flex items-center justify-center text-zinc-400 hover:text-white transition shrink-0"><i class="fa-solid fa-arrow-left"></i></a>
        <div>
            <h1 class="text-xl font-bold">{{ $user->exists ? 'Edit user' : 'New user' }}</h1>
            <p class="text-sm text-zinc-500">{{ $user->exists ? 'Update account details and permissions.' : 'Create a new admin or editor account.' }}</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-4 text-sm text-red-400 bg-red-500/10 border border-red-500/30 rounded-lg px-3 py-2">
            @foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <div class="grid lg:grid-cols-3 gap-4 items-start">
        {{-- LEFT: form --}}
        <form method="POST" action="{{ $user->exists ? '/admin/users/' . $user->id : '/admin/users' }}" @submit="loading = true" class="lg:col-span-2 space-y-4">
            @csrf
            @if ($user->exists) @method('PUT') @endif

            <div class="bg-zinc-900 rounded-xl border border-zinc-800 overflow-hidden">
                <div class="flex items-center gap-2.5 px-4 py-3 border-b border-zinc-800">
                    <span class="w-8 h-8 rounded-lg bg-blue-500/15 text-blue-400 flex items-center justify-center"><i class="fa-solid fa-user text-sm"></i></span>
                    <h2 class="text-sm font-semibold">Account details</h2>
                </div>
                <div class="p-4 space-y-4">
                    <div>
                        <label class="block text-xs text-zinc-400 mb-1">Name *</label>
                        <input type="text" name="name" x-model="name" required class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
                    </div>
                    <div>
                        <label class="block text-xs text-zinc-400 mb-1">Email *</label>
                        <input type="email" name="email" x-model="email" required class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
                    </div>
                    <div>
                        <label class="block text-xs text-zinc-400 mb-1">Role *</label>
                        <select name="role" x-model="role" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
                            <option value="editor">Editor — articles & predictions only</option>
                            <option value="admin">Admin — full access</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-zinc-400 mb-1">Bio <span class="text-zinc-600">(optional)</span></label>
                        <textarea name="bio" rows="3" placeholder="Short author bio shown on their public author page." class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">{{ old('bio', $user->bio) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="bg-zinc-900 rounded-xl border border-zinc-800 overflow-hidden">
                <div class="flex items-center gap-2.5 px-4 py-3 border-b border-zinc-800">
                    <span class="w-8 h-8 rounded-lg bg-amber-500/15 text-amber-400 flex items-center justify-center"><i class="fa-solid fa-lock text-sm"></i></span>
                    <h2 class="text-sm font-semibold">Security</h2>
                </div>
                <div class="p-4">
                    <label class="block text-xs text-zinc-400 mb-1">Password {{ $user->exists ? '(leave blank to keep current)' : '*' }}</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="password" {{ $user->exists ? '' : 'required' }} placeholder="{{ $user->exists ? '••••••••' : 'At least 6 characters' }}" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 pr-10 text-sm focus:outline-none focus:border-blue-500 transition">
                        <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-500 hover:text-white transition"><i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i></button>
                    </div>
                </div>
            </div>

            <button type="submit" :disabled="loading" class="bg-blue-600 hover:bg-blue-500 disabled:opacity-60 text-white font-semibold px-5 py-2.5 rounded-lg text-sm transition flex items-center gap-2">
                <i x-show="loading" x-cloak class="fa-solid fa-spinner fa-spin"></i>
                <span>{{ $user->exists ? 'Update user' : 'Create user' }}</span>
            </button>
        </form>

        {{-- RIGHT: preview + activity (outside form to avoid nesting the delete form) --}}
        <div class="space-y-4">
            <div class="bg-zinc-900 rounded-xl border border-zinc-800 p-5 text-center">
                <span class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-600 to-blue-500 flex items-center justify-center text-2xl font-bold mx-auto mb-3" x-text="(name || '?').charAt(0).toUpperCase()"></span>
                <div class="font-semibold truncate" x-text="name || 'New user'"></div>
                <div class="text-xs text-zinc-500 truncate" x-text="email || 'email@example.com'"></div>
                <span class="inline-block mt-2 text-[11px] px-2 py-0.5 rounded" :class="role === 'admin' ? 'bg-blue-500/15 text-blue-400' : 'bg-zinc-800 text-zinc-400'" x-text="role === 'admin' ? 'Admin' : 'Editor'"></span>
            </div>

            @if ($user->exists)
                <div class="bg-zinc-900 rounded-xl border border-zinc-800 overflow-hidden">
                    <div class="px-4 py-3 border-b border-zinc-800 text-sm font-semibold">Activity</div>
                    <div class="divide-y divide-zinc-800/60 text-sm">
                        <div class="flex items-center justify-between px-4 py-2.5"><span class="text-zinc-500">Articles</span><span class="font-medium">{{ $user->articles()->count() }}</span></div>
                        <div class="flex items-center justify-between px-4 py-2.5"><span class="text-zinc-500">Last login</span><span class="text-zinc-400">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</span></div>
                        <div class="flex items-center justify-between px-4 py-2.5"><span class="text-zinc-500">Joined</span><span class="text-zinc-400">{{ $user->created_at->format('d M Y') }}</span></div>
                    </div>
                </div>

                @if ($user->id !== auth()->id())
                    <form method="POST" action="/admin/users/{{ $user->id }}" onsubmit="return confirm('Delete this user permanently?')">
                        @csrf @method('DELETE')
                        <button class="w-full bg-red-500/10 hover:bg-red-500/20 text-red-400 text-sm font-medium px-4 py-2.5 rounded-lg transition flex items-center justify-center gap-2"><i class="fa-solid fa-trash"></i> Delete user</button>
                    </form>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection