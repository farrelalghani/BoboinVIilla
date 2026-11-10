<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (! $response instanceof Response) {
            return $response;
        }

        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

            if (
                $request->is('admin/*')
                || $request->is('admin')
                || $request->routeIs('admin.*')
                || $request->is('cek-pesanan')
                || $request->is('payment/*')
                || $request->is('invoice/*')
                || auth()->check()
            ) {
                $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
                $response->headers->set('Pragma', 'no-cache');
                $response->headers->set('Expires', '0');
            }
        }

        return $response;
    }
}
