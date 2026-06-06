<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>You're offline</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-zinc-950 text-white min-h-screen flex items-center justify-center p-6">
    <div class="text-center">
        <div class="text-5xl mb-3">📡</div>
        <h1 class="text-xl sm:text-2xl font-bold">You're offline</h1>
        <p class="text-zinc-500 text-sm mt-2 max-w-sm mx-auto">No internet connection. Please reconnect and try again.</p>
        <button onclick="location.reload()" class="inline-block bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition mt-6">Retry</button>
    </div>
</body>
</html>