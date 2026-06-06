<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login - LiveScore</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-zinc-950 text-white min-h-screen">
    <div class="min-h-screen flex">

        {{-- KIRI: branding (sembunyi di mobile) --}}
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-gradient-to-br from-zinc-900 via-blue-950/30 to-zinc-950 p-12 flex-col justify-center">
            <div class="absolute -top-24 -left-24 w-80 h-80 bg-blue-600/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-32 -right-10 w-96 h-96 bg-blue-700/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative">
                <div class="flex items-center gap-2.5 mb-14">
                    @if (! empty($settings['logo']))
                        <img src="{{ asset('storage/' . $settings['logo']) }}" alt="logo" class="h-9 w-auto">
                        <span class="text-xl font-medium text-zinc-400">Admin</span>
                    @else
                        <span class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-600/30">
                            <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2L3 14h7v8l10-12h-7z" /></svg>
                        </span>
                        <span class="text-xl font-bold">Live<span class="text-blue-500">Score</span> <span class="text-zinc-400 font-medium">Admin</span></span>
                    @endif
                </div>

                <h1 class="text-4xl font-extrabold leading-tight mb-4">Manage content<br>with <span class="text-blue-500">ease.</span></h1>
                <p class="text-zinc-400 max-w-md mb-10 leading-relaxed">Admin panel to manage news, leagues, users, and site settings — all in one place.</p>

                <div class="space-y-5 max-w-md">
                    @foreach ([
                        ['Articles', 'Write, edit & publish content', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                        ['Analytics', 'Track visitor traffic', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                        ['Users', 'Manage accounts & access', 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5a4 4 0 11-8 0 4 4 0 018 0zm6 3a3 3 0 10-2-5.3'],
                        ['Secure', 'Encrypted & protected sessions', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                    ] as [$t, $d, $icon])
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-lg bg-blue-600/15 border border-blue-600/20 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
                            </span>
                            <div>
                                <div class="text-sm font-semibold">{{ $t }} <span class="text-zinc-500 font-normal">— {{ $d }}</span></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- KANAN: form --}}
        <div class="w-full lg:w-1/2 flex items-center justify-center p-6">
            <div class="w-full max-w-sm">
                <div class="lg:hidden flex items-center gap-2 justify-center mb-8">
                    @if (! empty($settings['logo']))
                        <img src="{{ asset('storage/' . $settings['logo']) }}" alt="logo" class="h-8 w-auto">
                        <span class="text-lg font-medium text-zinc-400">Admin</span>
                    @else
                        <span class="w-9 h-9 rounded-lg bg-blue-600 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2L3 14h7v8l10-12h-7z" /></svg>
                        </span>
                        <span class="text-lg font-bold">Live<span class="text-blue-500">Score</span> Admin</span>
                    @endif
                </div>

                <h2 class="text-2xl font-bold mb-1">Welcome back</h2>
                <p class="text-sm text-zinc-500 mb-6">Sign in to your admin dashboard</p>

                @if ($errors->any())
                    <div class="mb-4 text-sm text-red-400 bg-red-500/10 border border-red-500/30 rounded-lg px-3 py-2">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="/admin/login" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-400 mb-1.5">Email</label>
                        <div class="relative">
                            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="Enter your email"
                                   class="w-full bg-zinc-900 border border-zinc-800 rounded-xl pl-9 pr-4 py-3 text-sm focus:outline-none focus:border-blue-500 transition placeholder-zinc-600">
                        </div>
                    </div>

                    <div x-data="{ show: false }">
                        <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-400 mb-1.5">Password</label>
                        <div class="relative">
                            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            <input :type="show ? 'text' : 'password'" name="password" required placeholder="Enter your password"
                                   class="w-full bg-zinc-900 border border-zinc-800 rounded-xl pl-9 pr-10 py-3 text-sm focus:outline-none focus:border-blue-500 transition placeholder-zinc-600">
                            <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-500 hover:text-zinc-300 transition">
                                <svg x-show="!show" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <svg x-show="show" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                            </button>
                        </div>
                    </div>

                    <label class="flex items-center gap-2 text-xs text-zinc-400">
                        <input type="checkbox" name="remember" class="rounded bg-zinc-900 border-zinc-700"> Remember me
                    </label>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-xl py-3 text-sm transition shadow-lg shadow-blue-600/20 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        Sign in
                    </button>
                </form>

                <p class="text-center text-xs text-zinc-600 mt-6">© {{ now()->year }} LiveScore — Admin Panel</p>
            </div>
        </div>
    </div>
</body>
</html>