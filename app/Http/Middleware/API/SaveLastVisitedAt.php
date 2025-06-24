<?php

namespace App\Http\Middleware\API;

use App\Models\Client;
use Closure;

class SaveLastVisitedAt
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \Illuminate\Http\Response $response
     *
     * @return void
     */
    public function terminate($request, $response)
    {
        try {
            $user = user();
            if ($user instanceof Client) {
                $user->last_visited_at = now();
                $user->save();
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
