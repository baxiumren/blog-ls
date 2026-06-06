<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Maintenance
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            Setting::get('maintenance_mode') === '1'
            && ! $request->is('admin', 'admin/*')
            && ! (auth()->check() && auth()->user()->is_admin)
        ) {
            return response()->view('maintenance', [], 503);
        }

        return $next($request);
    }
}