<?php

namespace App\Http\Middleware\API;

use Closure;
use Illuminate\Http\Response;

class UserEmailVerified
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
        $user = auth()->user();

        if ($user === null || $user->is_email_verified === false) {
            return abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
