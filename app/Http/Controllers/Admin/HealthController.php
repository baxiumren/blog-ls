<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function index()
    {
        try { DB::connection()->getPdo(); $db = true; } catch (\Throwable $e) { $db = false; }

        $storageLink     = file_exists(public_path('storage'));
        $storageWritable = is_writable(storage_path());
        $debug           = config('app.debug');
        $isProd          = app()->environment('production');

        $extensions = ['pdo', 'mbstring', 'openssl', 'curl', 'gd', 'fileinfo', 'json'];
        $missingExt = array_values(array_filter($extensions, fn ($e) => ! extension_loaded($e)));

        $checks = [
            ['PHP version', PHP_VERSION, version_compare(PHP_VERSION, '8.2', '>=') ? 'ok' : 'fail'],
            ['Laravel', app()->version(), 'info'],
            ['Environment', app()->environment(), 'info'],
            ['Debug mode', $debug ? 'On' : 'Off', ($debug && $isProd) ? 'warn' : 'ok'],
            ['Database', $db ? 'Connected (' . config('database.default') . ')' : 'Connection failed', $db ? 'ok' : 'fail'],
            ['Storage writable', $storageWritable ? 'Yes' : 'No', $storageWritable ? 'ok' : 'fail'],
            ['Storage symlink', $storageLink ? 'Linked' : 'Missing — run php artisan storage:link', $storageLink ? 'ok' : 'warn'],
            ['Cache driver', config('cache.default'), 'info'],
            ['Queue driver', config('queue.default'), 'info'],
            ['Mail driver', config('mail.default'), config('mail.default') === 'log' ? 'warn' : 'info'],
            ['PHP extensions', $missingExt ? 'Missing: ' . implode(', ', $missingExt) : 'All present', $missingExt ? 'fail' : 'ok'],
        ];

        $free  = @disk_free_space(base_path());
        $total = @disk_total_space(base_path());

        return view('admin.health', [
            'checks'   => $checks,
            'freeGb'   => $free ? round($free / 1073741824, 1) : null,
            'totalGb'  => $total ? round($total / 1073741824, 1) : null,
            'usedPct'  => ($total && $free) ? round((($total - $free) / $total) * 100) : null,
        ]);
    }
}