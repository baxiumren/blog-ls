@extends('admin.layout')
@section('title', 'Users')

@section('content')
    <div class="flex items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold">Users</h1>
            <p class="text-sm text-zinc-500 mt-0.5">{{ $stats['total'] }} user{{ $stats['total'] === 1 ? '' : 's' }} · {{ $stats['admins'] }} admin · {{ $stats['editors'] }} editor</p>
        </div>
        <a href="/admin/users/create" class="bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold px-4 py-2.5 rounded-lg transition flex items-center gap-1.5 shrink-0">
            <i class="fa-solid fa-plus"></i> New user
        </a>
    </div>

    {{-- Search / filter --}}
    <form method="GET" class="flex flex-col sm:flex-row gap-2 mb-4">
        <div class="relative flex-1">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500 text-sm"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name or email…"
                class="w-full bg-zinc-900 border border-zinc-800 rounded-lg pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
        </div>
        <select name="role" onchange="this.form.submit()" class="bg-zinc-900 border border-zinc-800 rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500">
            <option value="">All roles</option>
            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
            <option value="editor" {{ request('role') === 'editor' ? 'selected' : '' }}>Editor</option>
        </select>
        <button class="bg-zinc-800 hover:bg-zinc-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition">Search</button>
    </form>

    {{-- Table --}}
    <div class="bg-zinc-900 border border-zinc-800 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-xs text-zinc-500 border-b border-zinc-800">
                    <tr>
                        <th class="text-left font-medium px-4 py-3">User</th>
                        <th class="text-left font-medium px-4 py-3">Role</th>
                        <th class="text-left font-medium px-4 py-3">Articles</th>
                        <th class="text-left font-medium px-4 py-3 whitespace-nowrap">Last login</th>
                        <th class="text-left font-medium px-4 py-3 whitespace-nowrap">Joined</th>
                        <th class="text-right font-medium px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    @forelse ($users as $u)
                        <tr class="hover:bg-zinc-800/30 transition">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-600 to-blue-500 flex items-center justify-center text-sm font-bold shrink-0">{{ strtoupper(substr($u->name, 0, 1)) }}</span>
                                    <div class="min-w-0">
                                        <div class="font-medium truncate flex items-center gap-1.5">{{ $u->name }}@if ($u->id === auth()->id())<span class="text-[10px] bg-zinc-800 text-zinc-400 px-1.5 py-0.5 rounded">You</span>@endif</div>
                                        <div class="text-xs text-zinc-500 truncate">{{ $u->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if ($u->isAdmin())
                                    <span class="text-[11px] bg-blue-500/15 text-blue-400 px-2 py-0.5 rounded">Admin</span>
                                @else
                                    <span class="text-[11px] bg-zinc-800 text-zinc-400 px-2 py-0.5 rounded">Editor</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-zinc-400">{{ $u->articles_count }}</td>
                            <td class="px-4 py-3 text-zinc-400 whitespace-nowrap">{{ $u->last_login_at ? $u->last_login_at->diffForHumans() : 'Never' }}</td>
                            <td class="px-4 py-3 text-zinc-400 whitespace-nowrap">{{ $u->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="/admin/users/{{ $u->id }}/edit" class="w-7 h-7 rounded-md bg-zinc-800 hover:bg-zinc-700 flex items-center justify-center text-zinc-400 hover:text-white transition" title="Edit"><i class="fa-solid fa-pen text-xs"></i></a>
                                    @if ($u->id !== auth()->id())
                                        <form method="POST" action="/admin/users/{{ $u->id }}" onsubmit="return confirm('Delete this user?')">
                                            @csrf @method('DELETE')
                                            <button class="w-7 h-7 rounded-md bg-zinc-800 hover:bg-red-500/20 flex items-center justify-center text-zinc-400 hover:text-red-400 transition" title="Delete"><i class="fa-solid fa-trash text-xs"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-zinc-500 text-sm">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection