<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VendorCheck
{
    /**
     * Handle an incoming request.
     *
     * Checks if the authenticated user is a vendor.
     * If not, redirects to home or dashboard.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->is_vendor) {
            // If not a vendor, redirect to home
            return redirect()->route('home');
        }

        return $next($request);
    }
}
