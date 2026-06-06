<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class CronController extends Controller
{
    public function index()
    {
        $hb = Cache::get('cron.heartbeat') ? Carbon::parse(Cache::get('cron.heartbeat')) : null;
        $healthy = $hb && $hb->diffInSeconds(now()) < 180;

        $tasks = [
            ['name' => 'Heartbeat', 'schedule' => 'Every minute', 'last' => $hb],
            ['name' => 'Live scores (sync:today)', 'schedule' => 'Every 2 minutes', 'last' => Cache::get('cron.sync_today') ? Carbon::parse(Cache::get('cron.sync_today')) : null],
            ['name' => 'Fixtures (sync:fixtures)', 'schedule' => 'Hourly', 'last' => Cache::get('cron.sync_fixtures') ? Carbon::parse(Cache::get('cron.sync_fixtures')) : null],
        ];

        return view('admin.cron', [
            'healthy'   => $healthy,
            'hb'        => $hb,
            'tasks'     => $tasks,
            'command'   => '* * * * * cd ' . base_path() . ' && php artisan schedule:run >> /dev/null 2>&1',
        ]);
    }
}