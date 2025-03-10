<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictExecutives
{
    public function handle(Request $request, Closure $next): Response
    {
        $restrictedStatuses = ['Review', 'Updated', 'Revision']; // ❌ Executives cannot access these
        $status = ucfirst($request->route('status')); // Normalize case

        if (auth()->user()->hasRole('executive') && in_array($status, $restrictedStatuses)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
