<!DOCTYPE html>
<html lang="en" class="[color-scheme:dark]">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Installed</title>@vite(['resources/css/app.css'])</head>
<body class="bg-zinc-950 text-white min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md text-center">
        <div class="text-5xl mb-3">🎉</div>
        <h1 class="text-2xl font-bold">Installation complete!</h1>
        <p class="text-sm text-zinc-400 mt-2">Your site is ready. Log in to the admin to finish setup (logo, branding, domains).</p>
        <div class="flex gap-2 justify-center mt-6">
            <a href="/admin" class="bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">Go to Admin →</a>
            <a href="{{ $url }}" class="bg-zinc-800 hover:bg-zinc-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">View site</a>
        </div>
        <p class="text-xs text-zinc-600 mt-6">⚠️ Last step on the server: add the cron line for live scores (see DEPLOY notes).</p>
    </div>
</body>
</html>