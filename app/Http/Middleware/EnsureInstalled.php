<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureInstalled
{
    public function handle(Request $request, Closure $next)
    {
        if (! file_exists(storage_path('installed')) && ! $request->is('install*')) {
            return redirect('/install');
        }
        return $next($request);
    }
}