<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotVendor
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->is_vendor) {
            return redirect()->route('vendor.dashboard');
        }

        return $next($request);
    }
}
