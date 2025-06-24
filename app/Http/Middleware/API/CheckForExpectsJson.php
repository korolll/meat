<?php

namespace App\Http\Middleware\API;

use Closure;
use Illuminate\Http\Response;

class CheckForExpectsJson
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->expectsJson() === false) {
            return response('', Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
        }

        return $next($request);
    }
}
