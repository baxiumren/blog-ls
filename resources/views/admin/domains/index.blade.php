@extends('admin.layout')
@section('title', 'Domains')

@section('content')
    <div class="mb-5">
        <h1 class="text-xl font-bold">Domains</h1>
        <p class="text-sm text-zinc-500 mt-0.5">Add backup domains via Cloudflare — useful if your main domain gets blocked.</p>
    </div>

    @if (session('ok'))<div class="mb-4 bg-green-500/10 border border-green-500/30 text-green-400 text-sm px-4 py-3 rounded-lg flex items-center gap-2"><i class="fa-solid fa-circle-check"></i> {{ session('ok') }}</div>@endif
    @if (session('error'))<div class="mb-4 bg-red-500/10 border border-red-500/30 text-red-400 text-sm px-4 py-3 rounded-lg flex items-center gap-2"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>@endif
    @if ($errors->any())<div class="mb-4 bg-red-500/10 border border-red-500/30 text-red-400 text-sm px-4 py-3 rounded-lg">{{ $errors->first() }}</div>@endif

    @if (! $configured)
        <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4 mb-4 flex items-start gap-3">
            <i class="fa-solid fa-triangle-exclamation text-amber-400 mt-0.5"></i>
            <div class="text-sm">
                <div class="font-semibold text-amber-400">Cloudflare not connected</div>
                <p class="text-zinc-400 mt-0.5">Add your Cloudflare email, Global API key and Server IP in <a href="/admin/settings/cloudflare" class="text-blue-400 hover:text-white underline">Settings → Cloudflare</a> before adding domains.</p>
            </div>
        </div>
    @endif

    {{-- Add domain --}}
    <form method="POST" action="/admin/domains" class="flex flex-col sm:flex-row gap-2 mb-5">
        @csrf
        <div class="relative flex-1">
            <i class="fa-solid fa-globe absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500 text-sm"></i>
            <input type="text" name="domain" value="{{ old('domain') }}" placeholder="example.com" required
                class="w-full bg-zinc-900 border border-zinc-800 rounded-lg pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition" {{ $configured ? '' : 'disabled' }}>
        </div>
        <button type="submit" {{ $configured ? '' : 'disabled' }} class="bg-blue-600 hover:bg-blue-500 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition flex items-center gap-2 shrink-0">
            <i class="fa-solid fa-plus"></i> Add domain
        </button>
    </form>

    {{-- List --}}
    <div x-data="{ rOpen:false, rAction:'', rUrl:'', rAbs:'0', rType:'301', rHas:false }">
    <div class="bg-zinc-900 border border-zinc-800 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-xs text-zinc-500 border-b border-zinc-800">
                    <tr>
                        <th class="text-left font-medium px-4 py-3">Domain</th>
                        <th class="text-left font-medium px-4 py-3">Status</th>
                        <th class="text-left font-medium px-4 py-3">SSL</th>
                        <th class="text-left font-medium px-4 py-3">Nameservers</th>
                        <th class="text-right font-medium px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    @forelse ($domains as $d)
                        @php $sb = ['active' => 'bg-green-500/15 text-green-400', 'pending' => 'bg-amber-500/15 text-amber-400'][$d->status] ?? 'bg-red-500/15 text-red-400'; @endphp
                        <tr class="hover:bg-zinc-800/30 transition align-top">
                            <td class="px-4 py-3">
                                <div class="font-medium flex items-center gap-2">
                                    {{ $d->domain }}
                                    @if ($d->is_primary)<span class="text-[10px] bg-blue-500/15 text-blue-400 px-1.5 py-0.5 rounded inline-flex items-center gap-1"><i class="fa-solid fa-star text-[8px]"></i> Primary</span>@endif
                                </div>
                                @if ($d->message)<div class="text-[11px] text-zinc-600 mt-0.5">{{ $d->message }}</div>@endif
                                @if ($d->redirect_url)<div class="text-[11px] text-purple-400 mt-0.5"><i class="fa-solid fa-share text-[9px]"></i> → {{ $d->redirect_url }}</div>@endif
                            </td>
                            <td class="px-4 py-3"><span class="text-[11px] px-2 py-0.5 rounded capitalize {{ $sb }}">{{ $d->status }}</span></td>
                            <td class="px-4 py-3">
                                @if ($d->ssl_status === 'active')
                                    <span class="text-[11px] px-2 py-0.5 rounded bg-green-500/15 text-green-400 inline-flex items-center gap-1"><i class="fa-solid fa-lock text-[9px]"></i> HTTPS active</span>
                                @else
                                    <span class="text-[11px] px-2 py-0.5 rounded bg-amber-500/15 text-amber-400 inline-flex items-center gap-1"><i class="fa-solid fa-lock-open text-[9px]"></i> Pending SSL</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-zinc-400 font-mono">
                                @forelse ($d->name_servers ?? [] as $ns)<div>{{ $ns }}</div>@empty<span class="text-zinc-600">—</span>@endforelse
                            </td>
                            <td class="px-4 py-3">
                                <a href="https://{{ $d->domain }}" target="_blank" rel="noopener" class="w-7 h-7 rounded-md bg-zinc-800 hover:bg-zinc-700 flex items-center justify-center text-zinc-400 hover:text-white transition" title="Visit"><i class="fa-solid fa-arrow-up-right-from-square text-xs"></i></a>
                                <button type="button" @click="rOpen=true; rAction='/admin/domains/{{ $d->id }}/redirect'; rUrl=@js($d->redirect_url ?? ''); rAbs='{{ $d->redirect_absolute ? '1' : '0' }}'; rType='{{ $d->redirect_type ?: '301' }}'; rHas={{ $d->redirect_url ? 'true' : 'false' }}" class="w-7 h-7 rounded-md bg-zinc-800 hover:bg-purple-500/20 flex items-center justify-center text-zinc-400 hover:text-purple-400 transition" title="Redirect"><i class="fa-solid fa-share text-xs"></i></button>
                                <div class="flex items-center justify-end gap-1">
                                    @if (! $d->is_primary)
                                        <form method="POST" action="/admin/domains/{{ $d->id }}/primary">@csrf
                                            <button class="w-7 h-7 rounded-md bg-zinc-800 hover:bg-blue-500/20 flex items-center justify-center text-zinc-400 hover:text-blue-400 transition" title="Set as primary"><i class="fa-solid fa-star text-xs"></i></button>
                                        </form>
                                    @endif
                                    <form method="POST" action="/admin/domains/{{ $d->id }}/refresh">@csrf
                                        <button class="w-7 h-7 rounded-md bg-zinc-800 hover:bg-zinc-700 flex items-center justify-center text-zinc-400 hover:text-white transition" title="Refresh status"><i class="fa-solid fa-rotate text-xs"></i></button>
                                    </form>
                                    <form method="POST" action="/admin/domains/{{ $d->id }}" onsubmit="return confirm('Remove this domain from the list? (Cloudflare zone stays.)')">@csrf @method('DELETE')
                                        <button class="w-7 h-7 rounded-md bg-zinc-800 hover:bg-red-500/20 flex items-center justify-center text-zinc-400 hover:text-red-400 transition" title="Remove"><i class="fa-solid fa-trash text-xs"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-zinc-500 text-sm">No domains yet. Add one above.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
            {{-- Redirect modal --}}
            <div x-show="rOpen" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/60" @click="rOpen=false"></div>
                <div class="relative bg-zinc-900 border border-zinc-800 rounded-xl w-full max-w-md p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-lg">Redirect domain</h3>
                        <button @click="rOpen=false" class="text-zinc-500 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <form method="POST" :action="rAction" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs text-zinc-400 mb-1">Redirect URL *</label>
                            <input type="url" name="redirect_url" x-model="rUrl" required placeholder="https://abc.com" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-zinc-400 mb-1">Absolute</label>
                                <select name="redirect_absolute" x-model="rAbs" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm">
                                    <option value="0">No (keep path)</option>
                                    <option value="1">Yes (exact URL)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-zinc-400 mb-1">Type</label>
                                <select name="redirect_type" x-model="rType" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm">
                                    <option value="301">301 permanent</option>
                                    <option value="302">302 temporary</option>
                                    <option value="307">307 temporary</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <button type="button" x-show="rHas" @click="if(confirm('Remove redirect?')){ $refs.rClear.submit() }" class="text-xs text-red-400 hover:text-red-300">Remove redirect</button>
                            <div x-show="!rHas"></div>
                            <div class="flex gap-2">
                                <button type="button" @click="rOpen=false" class="text-sm text-zinc-400 hover:text-white px-3 py-2">Cancel</button>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold px-5 py-2 rounded-lg">Save</button>
                            </div>
                        </div>
                    </form>
                    <form x-ref="rClear" method="POST" :action="rAction">@csrf<input type="hidden" name="clear" value="1"></form>
                </div>
            </div>
        </div>
    

    <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4 mt-4 text-xs text-zinc-500">
        <p class="font-semibold text-zinc-400 mb-1"><i class="fa-solid fa-circle-info"></i> After adding a domain:</p>
        <p>1. Go to your registrar and set the <b>nameservers</b> shown above. 2. Click <i class="fa-solid fa-rotate"></i> to refresh — status turns <span class="text-green-400">active</span> once Cloudflare detects them (can take minutes to hours). Server IP target: <code class="text-zinc-300">{{ $vpsIp ?: 'not set' }}</code></p>
    </div>
@endsection