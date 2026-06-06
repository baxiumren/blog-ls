<!DOCTYPE html>
<html lang="en" class="[color-scheme:dark]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Install</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-zinc-950 text-white min-h-screen py-10 px-4">
    <div class="max-w-2xl mx-auto">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold">⚽ Installation</h1>
            <p class="text-sm text-zinc-500 mt-1">Set up your site in one step — no manual config needed.</p>
        </div>

        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4 mb-4">
            <div class="text-sm font-semibold mb-2">Server requirements</div>
            <div class="grid sm:grid-cols-2 gap-1.5 text-sm">
                @foreach ($requirements as $label => $ok)
                    <div class="flex items-center gap-2">
                        <span class="{{ $ok ? 'text-green-400' : 'text-red-400' }}">{!! $ok ? '&check;' : '&times;' !!}</span>
                        <span class="{{ $ok ? 'text-zinc-300' : 'text-red-400' }}">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
            @unless ($ready)<p class="text-xs text-red-400 mt-2">Fix the red items first (usually folder permissions: chmod -R 775 storage database).</p>@endunless
        </div>

        @if ($err->any())
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-sm rounded-xl px-4 py-3 mb-4">
                @foreach ($err->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
        @endif

        <form method="POST" action="/install" class="space-y-4">
            <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4 space-y-3">
                <div class="text-sm font-semibold text-blue-400">1 · Site</div>
                <input name="site_name" value="{{ $old['site_name'] ?? '' }}" placeholder="Site name (e.g. SkorBola)" required class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm">
                <input name="site_url" value="{{ $old['site_url'] ?? '' }}" placeholder="https://yourdomain.com" required class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm">
                <select name="timezone" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm">
                    @foreach (['Asia/Jakarta','Asia/Makassar','Asia/Jayapura','UTC','Asia/Singapore','Asia/Bangkok','Europe/London'] as $tz)
                        <option value="{{ $tz }}" {{ ($old['timezone'] ?? 'Asia/Jakarta') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                    @endforeach
                </select>
            </div>

            <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4 space-y-3">
                <div class="text-sm font-semibold text-blue-400">2 · Admin account</div>
                <input name="admin_name" value="{{ $old['admin_name'] ?? '' }}" placeholder="Your name" required class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm">
                <input name="admin_email" type="email" value="{{ $old['admin_email'] ?? '' }}" placeholder="admin@email.com" required class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm">
                <input name="admin_password" type="password" placeholder="Password (min 6)" required class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm">
                <input name="admin_password_confirmation" type="password" placeholder="Confirm password" required class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm">
            </div>

            <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4 space-y-3">
                <div class="text-sm font-semibold text-blue-400">3 · API-Football <span class="text-zinc-600 font-normal">— required</span></div>
                <input name="api_football_key" value="{{ $old['api_football_key'] ?? '' }}" placeholder="Your API-Football key" required class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm font-mono">
            </div>

            <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4 space-y-3">
                <div class="text-sm font-semibold text-blue-400">4 · Cloudflare <span class="text-zinc-600 font-normal">— optional (anti-block backup domains)</span></div>
                <input name="cf_email" type="email" value="{{ $old['cf_email'] ?? '' }}" placeholder="Cloudflare account email" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm">
                <input name="cf_api_key" type="password" placeholder="Global API key" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm font-mono">
                <input name="vps_ip" value="{{ $old['vps_ip'] ?? '' }}" placeholder="Server IP (A record target)" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm">
            </div>

            <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4 space-y-3">
                <div class="text-sm font-semibold text-blue-400">5 · Email / SMTP <span class="text-zinc-600 font-normal">— optional</span></div>
                <input name="mail_host" value="{{ $old['mail_host'] ?? '' }}" placeholder="SMTP host (e.g. smtp-relay.brevo.com)" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm">
                <div class="grid grid-cols-2 gap-3">
                    <input name="mail_port" value="{{ $old['mail_port'] ?? '587' }}" placeholder="Port" class="bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm">
                    <input name="mail_from" type="email" value="{{ $old['mail_from'] ?? '' }}" placeholder="From email" class="bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm">
                </div>
                <input name="mail_username" value="{{ $old['mail_username'] ?? '' }}" placeholder="SMTP username" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm">
                <input name="mail_password" type="password" placeholder="SMTP password" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm">
            </div>

            <button type="submit" {{ $ready ? '' : 'disabled' }} class="w-full bg-blue-600 hover:bg-blue-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold py-3 rounded-lg transition">Install now →</button>
        </form>
    </div>
</body>
</html>