<?php

namespace App\Http\Middleware;

use App\Models\Domain;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DomainRedirect
{
    public function handle(Request $request, Closure $next)
    {
        $redirects = Cache::remember('domain.redirects', 3600, function () {
            return Domain::whereNotNull('redirect_url')->where('redirect_url', '!=', '')
                ->get(['domain', 'redirect_url', 'redirect_type', 'redirect_absolute'])
                ->keyBy('domain');
        });

        $host = $request->getHost();

        if ($r = $redirects->get($host)) {
            $target = rtrim($r->redirect_url, '/');
            if (! $r->redirect_absolute) {
                $target .= $request->getRequestUri();
            }
            // avoid redirecting to itself (loop guard)
            if (parse_url($target, PHP_URL_HOST) !== $host) {
                return redirect()->away($target, (int) ($r->redirect_type ?: 301));
            }
        }

        return $next($request);
    }
}