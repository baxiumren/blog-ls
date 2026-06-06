@extends('admin.layout')
@section('title', 'My profile')

@section('content')
    <div class="mb-5">
        <h1 class="text-xl font-bold">My profile</h1>
        <p class="text-sm text-zinc-500 mt-0.5">Manage your account details.</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 text-sm text-red-400 bg-red-500/10 border border-red-500/30 rounded-lg px-3 py-2 max-w-md">
            @foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="/admin/profile" class="space-y-4 max-w-md" x-data="{ loading: false, show: false }" @submit="loading = true">
        @csrf
        @method('PUT')
        <div class="bg-zinc-900 rounded-xl border border-zinc-800 p-4 space-y-4">
            <div class="flex items-center gap-3 pb-1">
                <span class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-600 to-blue-500 flex items-center justify-center text-lg font-bold shrink-0">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                <div>
                    <div class="font-semibold">{{ $user->name }}</div>
                    <span class="text-[10px] {{ $user->isAdmin() ? 'bg-blue-500/15 text-blue-400' : 'bg-zinc-800 text-zinc-400' }} px-2 py-0.5 rounded">{{ $user->isAdmin() ? 'Admin' : 'Editor' }}</span>
                </div>
            </div>
            <div>
                <label class="block text-xs text-zinc-400 mb-1">Name *</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
            </div>
            <div>
                <label class="block text-xs text-zinc-400 mb-1">Email *</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
            </div>
        </div>

        <div class="bg-zinc-900 rounded-xl border border-zinc-800 p-4 space-y-4">
            <h2 class="text-sm font-semibold">Change password <span class="text-zinc-600 font-normal">(optional)</span></h2>
            <div>
                <label class="block text-xs text-zinc-400 mb-1">New password</label>
                <div class="relative">
                    <input :type="show ? 'text' : 'password'" name="password" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 pr-10 text-sm focus:outline-none focus:border-blue-500 transition">
                    <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-500 hover:text-white transition">
                        <svg x-show="!show" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        <svg x-show="show" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-xs text-zinc-400 mb-1">Confirm new password</label>
                <input type="password" name="password_confirmation" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
            </div>
        </div>

        <button type="submit" :disabled="loading" class="bg-blue-600 hover:bg-blue-500 disabled:opacity-60 disabled:cursor-not-allowed text-white font-semibold px-5 py-2.5 rounded-lg text-sm transition flex items-center gap-2">
            <svg x-show="loading" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            <span>Save changes</span>
        </button>
    </form>
@endsection