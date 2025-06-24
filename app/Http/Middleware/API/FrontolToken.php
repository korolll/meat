<?php

namespace App\Http\Middleware\API;

use Closure;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class FrontolToken
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
        if ($token) {
            $authHeader = $request->header('Authorization', '');
            $authHash = Str::replaceFirst('FrontolAuth ', '', $authHeader);
            $expectedHash = md5($token . $request->getContent());
            if ($authHash !== $expectedHash) {
                abort(Response::HTTP_UNAUTHORIZED);
            }
        }

        return $next($request);
    }
}
