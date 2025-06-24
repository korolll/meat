<?php

namespace App\Http\Middleware\API;

use Closure;

class ShouldUseGuard
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        auth()->shouldUse($guard ?: config('auth.defaults.guard'));

        return $next($request);
    }
}
