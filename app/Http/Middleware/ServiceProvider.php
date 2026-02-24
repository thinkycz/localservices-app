<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ServiceProvider
{
    /**
     * Handle an incoming request.
     *
     * Checks if the authenticated user is a service provider.
     * If not, redirects to home or dashboard.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->is_service_provider) {
            // If not a service provider, redirect to home
            return redirect()->route('home');
        }

        return $next($request);
    }
}

