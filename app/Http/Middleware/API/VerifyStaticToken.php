<?php

namespace App\Http\Middleware\API;

use Closure;
use Illuminate\Http\Response;

class VerifyStaticToken
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string $token
     * @return mixed
     */
    public function handle($request, Closure $next, string $token)
    {
        if ($token && $request->bearerToken() !== $token) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
