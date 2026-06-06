<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPageView
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Cuma rekam GET halaman publik (skip admin, search, sitemap, ajax)
        if ($request->isMethod('GET')
            && ! $request->ajax()
            && ! $request->is('admin*', 'search', 'sitemap.xml', 'robots.txt')) {
            \App\Models\PageView::create([
                'path'       => '/' . ltrim($request->path(), '/'),
                'visitor'    => substr(hash('sha256', $request->ip() . $request->userAgent()), 0, 40),
                'referrer'   => $request->headers->get('referer'),
                'created_at' => now(),
            ]);
        }

        return $response;
    }
}
